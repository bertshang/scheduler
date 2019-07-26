<?php

namespace Bertshang\Scheduler\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class SchedulerEventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        'Bertshang\Scheduler\Events\Created'     => ['Bertshang\Scheduler\Listeners\BustCache'],
        'Bertshang\Scheduler\Events\Updated'     => ['Bertshang\Scheduler\Listeners\BustCache'],
        'Bertshang\Scheduler\Events\Activated'   => ['Bertshang\Scheduler\Listeners\BustCache'],
        'Bertshang\Scheduler\Events\Deactivated' => ['Bertshang\Scheduler\Listeners\BustCache'],
    ];
}
