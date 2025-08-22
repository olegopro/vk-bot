<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'first_name',
        'last_name',
        'owner_id',
        'item_id',
        'error_message',
        'status',
        'is_cyclic',
        'run_at'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
