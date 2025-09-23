<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rest extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'rest_start_at',
        'rest_end_at',
    ];

    // 日付カラムとして自動キャスト
    protected $dates = [
        'rest_start_at',
        'rest_end_at',
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
