<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_application_id',
        'rest_id',
        'rest_start_at',
        'rest_end_at',
    ];

        public function rest()
    {
        return $this->belongsTo(Rest::class);
    }

    public function work_applications()
    {
        return $this->belongsTo(WorkApplication::class);
    }
}
