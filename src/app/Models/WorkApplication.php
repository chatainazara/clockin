<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'clock_in_at',
        'clock_out_at',
        'reason',
        'approve_at'
    ];

    public function work()
    {
        return $this->belongsTo(Work::class);
    }

    public function rest_applications()
    {
        return $this->hasMany(RestApplication::class);
    }
}
