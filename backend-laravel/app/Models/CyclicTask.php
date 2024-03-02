<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method void decrement($column)
 * @method void increment($column)
 */
class CyclicTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'total_task_count',
        'remaining_tasks_count',
        'tasks_per_hour',
        'likes_distribution',
        'status',
        'started_at'
    ];
}
