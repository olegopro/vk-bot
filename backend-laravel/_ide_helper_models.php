<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * 
 *
 * @property int $account_id
 * @property string $access_token
 * @property string $screen_name
 * @property string $first_name
 * @property string $last_name
 * @property string $bdate
 * @method static \Database\Factories\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereBdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereScreenName($value)
 */
	class Account extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int $account_id
 * @property int $total_task_count
 * @property int $remaining_tasks_count
 * @property int $tasks_per_hour
 * @property string|null $likes_distribution
 * @property array|null $selected_times
 * @property string $status
 * @property string|null $started_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Account $account
 * @property-read string|null $first_name
 * @property-read string|null $last_name
 * @method static \Database\Factories\CyclicTaskFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask query()
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereLikesDistribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereRemainingTasksCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereSelectedTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereTasksPerHour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereTotalTaskCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CyclicTask whereUpdatedAt($value)
 */
	class CyclicTask extends \Eloquent {}
}

namespace App\Models{
/**
 * 
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
 */
	class Settings extends \Eloquent {}
}

namespace App\Models{
/**
 * 
 *
 * @property int $id
 * @property int|null $job_id
 * @property int $account_id
 * @property int $owner_id
 * @property string $first_name
 * @property string $last_name
 * @property int $item_id
 * @property string|null $error_message
 * @property string $status
 * @property int|null $is_cyclic
 * @property string|null $run_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Account|null $account
 * @method static \Database\Factories\TaskFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereErrorMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereIsCyclic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereRunAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 */
	class Task extends \Eloquent {}
}

