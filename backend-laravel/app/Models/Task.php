<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property int $account_id
 * @property string $payload
 * @property int $attempts
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\TaskFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Account $account
 * @property int $owner_id
 * @property int $item_id
 * @property int $attempt_count
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAttemptCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 */
class Task extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'first_name', 'last_name', 'owner_id', 'item_id', 'status'];
    // public $timestamps = false;

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
