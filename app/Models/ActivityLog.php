<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $table = 'sys_activity_logs';

    protected $fillable = ['log_name', 'description', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'properties', 'batch_uuid'];

    protected $casts = ['properties' => 'array'];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
