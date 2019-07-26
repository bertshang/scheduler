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
use Illuminate\Console\Scheduling\Schedule;
use Carbon\Carbon;
use Cron\CronExpression;
class EloquentTaskRepository implements TaskInterface
{

    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }

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
    public function update(array $input, $task)
    {
        $task = $this->find($task);

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

        $task->fill(['is_active' => 1])->save();

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

        $task->fill(['is_active' => 0])->save();

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

            file_put_contents(storage_path($task->getMutexName()), Artisan::output());
        } catch (\Exception $e) {
            file_put_contents(storage_path($task->getMutexName()), $e->getMessage());
        }

        Executed::dispatch($task, $start);

        return $task;
    }

    //同步定时任务数据
    public function sysnc()
    {
        if (count($this->schedule->events()) > 0) {
            $events = collect($this->schedule->events())->map(function ($event) {
                return [
                    'description'   => $event->description ?: 'N/A',
                    'command'       => ltrim(strtok(str_after($event->command, "'artisan'"), ' ')),
                    'expression'      => $event->expression,
                    'upcoming'      => $this->upcoming($event),
                    'timezone'      => $event->timezone ?: config('app.timezone'),
                    'overlaps'      => $event->withoutOverlapping ? 'No' : 'Yes',
                    'maintenance'   => $event->evenInMaintenanceMode ? 'Yes' : 'No',
                ];
            });

            Task::create($events);
        }
    }

    /**
     * Get Upcoming schedule.
     *
     * @return bool
     */
    protected function upcoming($event)
    {
        $date = Carbon::now();

        if ($event->timezone) {
            $date->setTimezone($event->timezone);
        }

        return (CronExpression::factory($event->expression)->getNextRunDate($date->toDateTimeString()))->format('Y-m-d H:i:s');
    }
}
