<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['account_id', 'access_token', 'screen_name', 'first_name', 'last_name', 'bdate'];
    protected $primaryKey = 'account_id';
    public $timestamps = false;
    public $incrementing = false;
}
