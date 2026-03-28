<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $guardian = User::where('email', 'youssef@bank.ma')->first();
        $minor    = User::where('email', 'amine@bank.ma')->first();
        $sara     = User::where('email', 'sara@bank.ma')->first();
        $omar     = User::where('email', 'omar@bank.ma')->first();

        $courant1 = Account::create([
            'rib'                 => 'MA' . strtoupper(uniqid()) . rand(100, 999),
            'type'                => 'COURANT',
            'status'              => 'ACTIVE',
            'balance'             => 12500.00,
            'overdraft_limit'     => 2000.00,
            'monthly_fee'         => 50.00,
            'daily_transfer_limit'=> 10000.00,
        ]);
        $courant1->users()->attach($sara->id, [
            'relation_type'    => 'owner',
            'accepted_closure' => false,
        ]);

        $courantJoint = Account::create([
            'rib'                 => 'MA' . strtoupper(uniqid()) . rand(100, 999),
            'type'                => 'COURANT',
            'status'              => 'ACTIVE',
            'balance'             => 8000.00,
            'overdraft_limit'     => 1000.00,
            'monthly_fee'         => 50.00,
            'daily_transfer_limit'=> 10000.00,
        ]);
        $courantJoint->users()->attach($sara->id, [
            'relation_type'    => 'owner',
            'accepted_closure' => false,
        ]);
        $courantJoint->users()->attach($omar->id, [
            'relation_type'    => 'owner',
            'accepted_closure' => false,
        ]);

        $epargne = Account::create([
            'rib'                 => 'MA' . strtoupper(uniqid()) . rand(100, 999),
            'type'                => 'EPARGNE',
            'status'              => 'ACTIVE',
            'balance'             => 35000.00,
            'overdraft_limit'     => 0.00,
            'interest_rate'       => 3.50,
            'monthly_fee'         => 0.00,
            'daily_transfer_limit'=> 10000.00,
        ]);
        $epargne->users()->attach($omar->id, [
            'relation_type'    => 'owner',
            'accepted_closure' => false,
        ]);

        $mineur = Account::create([
            'rib'                 => 'MA' . strtoupper(uniqid()) . rand(100, 999),
            'type'                => 'MINEUR',
            'status'              => 'ACTIVE',
            'balance'             => 5000.00,
            'overdraft_limit'     => 0.00,
            'interest_rate'       => 2.00,
            'monthly_fee'         => 0.00,
            'daily_transfer_limit'=> 5000.00,
        ]);
        $mineur->users()->attach($guardian->id, [
            'relation_type'    => 'guardian',
            'accepted_closure' => false,
        ]);
        $mineur->users()->attach($minor->id, [
            'relation_type'    => 'owner',
            'accepted_closure' => false,
        ]);

        $blocked = Account::create([
            'rib'                 => 'MA' . strtoupper(uniqid()) . rand(100, 999),
            'type'                => 'COURANT',
            'status'              => 'BLOCKED',
            'balance'             => 10.00,
            'overdraft_limit'     => 0.00,
            'monthly_fee'         => 50.00,
            'block_reason'        => 'Insufficient balance for monthly fee',
            'daily_transfer_limit'=> 10000.00,
        ]);
        $blocked->users()->attach($omar->id, [
            'relation_type'    => 'owner',
            'accepted_closure' => false,
        ]);
    }
}