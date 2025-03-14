<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class GroupInvitation extends Model
{
    use HasFactory;
    
    public $timestamps = true;
    protected $fillable = ['group_id', 'email', 'token', 'status', 'expires_at'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function isExpired()
    {
        return $this->expires_at && Carbon::parse($this->expires_at)->isPast();
    }
}
