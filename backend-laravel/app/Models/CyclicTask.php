<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CyclicTask extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'tasks_per_hour', 'tasks_count', 'status'];
}
