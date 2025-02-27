<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'category_id',
        'amount',
        'type',
        'description',
        'actor',
        'transaction_time',
        'proof'
    ];

    /**
     * The user who created the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The group in which the transaction was recorded.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * The category of the transaction.
     */
    public function category()
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id');
    }
}
