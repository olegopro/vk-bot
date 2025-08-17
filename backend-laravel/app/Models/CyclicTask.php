<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CyclicTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'total_task_count',
        'remaining_tasks_count',
        'tasks_per_hour',
        'likes_distribution',
        'selected_times',
        'status',
        'started_at'
    ];

    protected $casts = [
        'selected_times' => 'array',
    ];

    protected $appends = ['first_name', 'last_name']; // Добавляем эти поля в сериализованный вывод
    protected $hidden = ['account']; // Скрыть информацию об аккаунте из сериализованного вывода

    /**
     * Определяет отношение "принадлежит" (belongs to) к модели Account.
     * Устанавливает связь с аккаунтом ВКонтакте, от имени которого выполняется задача.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Отношение к модели Account
     */
    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Accessor для получения имени владельца аккаунта.
     *
     * Этот метод позволяет получить имя владельца аккаунта через
     * свойство first_name без необходимости явно загружать связь с аккаунтом.
     *
     * @return string|null Имя владельца аккаунта или null, если аккаунт не найден
     */
    public function getFirstNameAttribute()
    {
        return $this->account->first_name ?? null;
    }

    /**
     * Accessor для получения фамилии владельца аккаунта.
     *
     * Этот метод позволяет получить фамилию владельца аккаунта через
     * свойство last_name без необходимости явно загружать связь с аккаунтом.
     *
     * @return string|null Фамилия владельца аккаунта или null, если аккаунт не найден
     */
    public function getLastNameAttribute()
    {
        return $this->account->last_name ?? null;
    }
}
