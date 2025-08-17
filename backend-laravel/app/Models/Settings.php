<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = ['show_followers', 'show_friends', 'task_timeout'];
    protected $casts = [
        'show_followers' => 'boolean',
        'show_friends'   => 'boolean',
    ];

    public $timestamps = false;
}
