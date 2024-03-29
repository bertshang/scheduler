<?php

namespace Bertshang\Scheduler\Repositories;

use Bertshang\Scheduler\Task;
use Bertshang\Scheduler\Events\Created;
use Bertshang\Scheduler\Events\Deleted;
use Bertshang\Scheduler\Events\Updated;
use Bertshang\Scheduler\Events\Creating;
use Bertshang\Scheduler\Events\Executed;
use Bertshang\Scheduler\Events\Updating;
use Bertshang\Scheduler\Events\Activated;
use Bertshang\Scheduler\Events\Deactivated;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Bertshang\Scheduler\Contracts\TaskInterface;

class EloquentTaskRepository implements TaskInterface
{
    /**
     * Return task eloquent builder.
     *
     * @return Task
     */
    public function builder()
    {
        return new Task;
    }

    /**
     * Find a task by id.
     *
     * @param int|Task $id
     * @return int|Task
     */
    public function find($id)
    {
        if ($id instanceof Task) {
            return $id;
        }

        return Cache::rememberForever('totem.task.'.$id, function () use ($id) {
            return Task::find($id);
        });
    }

    /**
     * Find all tasks.
     *
     * @return mixed
     */
    public function findAll()
    {
        return Cache::rememberForever('totem.tasks.all', function () {
            return Task::all();
        });
    }

    /**
     * Find all active tasks.
     *
     * @return mixed
     */
    public function findAllActive()
    {
        return Cache::rememberForever('totem.tasks.active', function () {
            return $this->findAll()->filter(function ($task) {
                return $task->is_active;
            });
        });
    }

    /**
     * Create a new task.
     *
     * @param array $input
     * @return bool|Task
     */
    public function store(array $input)
    {
        $task = new Task;

        if (Creating::dispatch($input) === false) {
            return false;
        }

        $task->fill(array_only($input, $task->getFillable()))->save();

        Created::dispatch($task);

        return $task;
    }

    /**
     * Update the given task.
     *
     * @param array $input
     * @param Task $task
     * @return bool|int|Task
     */
    public function update(array $input)
    {
        $task = $this->find($input['id']);
        unset($input['id']);
        if (Updating::dispatch($input, $task) === false) {
            return false;
        }

        $task->fill(array_only($input, $task->getFillable()))->save();

        Updated::dispatch($task);

        return $task;
    }

    /**
     * Delete the given task.
     *
     * @param int|Task $id
     * @return bool
     */
    public function destroy($id)
    {
        $task = $this->find($id);

        if ($task) {
            Deleted::dispatch($task);
            $task->delete();

            return true;
        }

        return false;
    }

    /**
     * Activate the given task.
     *
     * @param $input
     * @return int|Task
     */
    public function activate($input)
    {
        $task = $this->find($input['task_id']);

        $task->fill(['is_active' => true])->save();

        Activated::dispatch($task);

        return $task;
    }

    /**
     * Deactive the given task.
     *
     * @param $id
     * @return int|Task
     */
    public function deactivate($id)
    {
        $task = $this->find($id);

        $task->fill(['is_active' => false])->save();

        Deactivated::dispatch($task);

        return $task;
    }

    /**
     * Execute a given task.
     *
     * @param $id
     * @return int|Task
     */
    public function execute($id)
    {
        $task = $this->find($id);

        $start = microtime(true);

        try {

            Artisan::call($task->command, $task->compileParameters());
            $output = Artisan::output();
            \Log::info("command results:".$output);
             return true;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::info("command results error:".$error);

            return false;
        }

        Executed::dispatch($task, $start);

    }
}
