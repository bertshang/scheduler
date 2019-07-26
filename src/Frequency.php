<?php

namespace Bertshang\Scheduler;

use Bertshang\Scheduler\Traits\HasParameters;

class Frequency extends TotemModel
{
    use HasParameters;

    protected $table = 'task_frequencies';

    protected $fillable = [
        'label',
        'interval',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
