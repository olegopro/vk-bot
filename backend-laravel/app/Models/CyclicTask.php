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

    protected $appends = ['first_name', 'last_name']; // Добавляем эти поля в сериализованный вывод
    protected $hidden = ['account']; // Скрыть информацию об аккаунте из сериализованного вывода


    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    // Аксессор для first_name
    public function getFirstNameAttribute()
    {
        return $this->account->first_name ?? null;
    }

    // Аксессор для last_name
    public function getLastNameAttribute()
    {
        return $this->account->last_name ?? null;
    }
}
