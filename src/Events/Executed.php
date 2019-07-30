<?php

namespace Bertshang\Scheduler\Events;

use Bertshang\Scheduler\Task;

class Executed extends Event
{
    /**
     * Executed constructor.
     *
     * @param Task $task
     * @param string $started
     */
    public function __construct(Task $task, $started)
    {
        parent::__construct($task);

        $time_elapsed_secs = microtime(true) - $started;
        $task->results()->create([
            'duration'  => $time_elapsed_secs * 1000,
            'result'    => $output,
        ]);
    }
}
