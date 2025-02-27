<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id', 
        'type', 
        'name'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}

