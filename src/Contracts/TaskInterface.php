<?php

namespace Bertshang\Scheduler\Contracts;

interface TaskInterface
{
    /**
     * Returns Eloquent Builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function builder();

    /**
     * Returns a task by its primary key.
     *
     * @param  int|\Bertshang\Scheduler\Task  $id
     * @return \Bertshang\Scheduler\Task
     */
    public function find($id);

    /**
     * Returns all tasks.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAll();

    /**
     * Returns all active tasks.
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function findAllActive();

    /**
     * Creates a new task with the given data.
     *
     * @param  array $input
     * @return \Bertshang\Scheduler\Task
     */
    public function store(array $input);

    /**
     * Updates the given task with the given data.
     *
     * @param  array $input
     * @param  \Bertshang\Scheduler\Task  $task
     * @return \Bertshang\Scheduler\Task
     */
    public function update(array $input);

    /**
     * Deletes the given task.
     *
     * @param  int|\Bertshang\Scheduler\Task  $id
     * @return bool
     */
    public function destroy($id);

    /**
     * Executes the given task.
     *
     * @param  int|\Bertshang\Scheduler\Task  $id
     * @return bool
     */
    public function execute($id);
}
