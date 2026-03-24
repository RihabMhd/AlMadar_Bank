<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use HasFactory;
    protected $fillable = ['sender_id', 'receiver_id', 'initiated_by', 'amount', 'reason', 'status'];

    public function senderAccount()
    {
        return $this->belongsTo(Account::class, 'sender_id');
    }

    public function receiverAccount()
    {
        return $this->belongsTo(Account::class, 'receiver_id');
    }
    public function initiator()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
