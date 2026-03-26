<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;
    protected $fillable = ['rib','type','balance','status','overdraft_limit','interest_rate','monthly_fee','block_reason'];


    public function users(){
        return $this->belongsToMany(User::class,'account_user')->withPivot('id', 'relation_type', 'accepted_closure');
    }

    public function transactions(){
        return $this->hasMany(Transaction::class);
    }

    public function sentTransfers()
    {
        return $this->hasMany(Transfer::class, 'sender_id');
    }

    public function receivedTransfers()
    {
        return $this->hasMany(Transfer::class, 'receiver_id');
    }

}
