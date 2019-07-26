<?php

namespace Bertshang\Scheduler;

use Illuminate\Database\Eloquent\Model;

class TotemModel extends Model
{
    /**
     * @return mixed
     */
    public function getTable()
    {
        return config('totem.table_prefix').parent::getTable();
    }
}
