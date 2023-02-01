<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Settings
 *
 * @property int $id
 * @property int $show_followers
 * @property int $show_friends
 * @property int $task_timeout
 * @method static \Illuminate\Database\Eloquent\Builder|Settings newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Settings newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Settings query()
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereShowFollowers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereShowFriends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Settings whereTaskTimeout($value)
 * @mixin \Eloquent
 */
class Settings extends Model
{
    use HasFactory;

    protected $fillable = ['show_followers', 'show_friends', 'task_timeout'];
    public $timestamps = false;
}
