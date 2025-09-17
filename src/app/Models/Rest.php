<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'break_start_at',
        'break_end_at',
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
