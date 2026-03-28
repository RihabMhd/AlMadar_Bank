<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $sara  = User::where('email', 'sara@bank.ma')->first();
        $omar  = User::where('email', 'omar@bank.ma')->first();
        $guardian = User::where('email', 'youssef@bank.ma')->first();

        $courant1    = Account::whereHas('users', fn($q) => $q->where('user_id', $sara->id))
                              ->where('type', 'COURANT')->first();
        $epargne     = Account::whereHas('users', fn($q) => $q->where('user_id', $omar->id))
                              ->where('type', 'EPARGNE')->first();
        $mineur      = Account::where('type', 'MINEUR')->first();
        $blocked     = Account::where('status', 'BLOCKED')->first();

        $transfer1 = Transfer::create([
            'sender_id'    => $courant1->id,
            'receiver_id'  => $epargne->id,
            'initiated_by' => $sara->id,
            'amount'       => 1500.00,
            'reason'       => 'Monthly savings',
            'status'       => 'COMPLETED',
        ]);

        Transaction::create([
            'account_id'  => $courant1->id,
            'transfer_id' => $transfer1->id,
            'type'        => 'TRANSFER_OUT',
            'amount'      => 1500.00,
            'label'       => 'Transfer to ' . $epargne->rib,
            'created_at'  => now()->subDays(10),
        ]);

        Transaction::create([
            'account_id'  => $epargne->id,
            'transfer_id' => $transfer1->id,
            'type'        => 'TRANSFER_IN',
            'amount'      => 1500.00,
            'label'       => 'Transfer from ' . $courant1->rib,
            'created_at'  => now()->subDays(10),
        ]);

        $transfer2 = Transfer::create([
            'sender_id'    => $courant1->id,
            'receiver_id'  => $mineur->id,
            'initiated_by' => $guardian->id,
            'amount'       => 500.00,
            'reason'       => 'Pocket money',
            'status'       => 'COMPLETED',
        ]);

        Transaction::create([
            'account_id'  => $courant1->id,
            'transfer_id' => $transfer2->id,
            'type'        => 'TRANSFER_OUT',
            'amount'      => 500.00,
            'label'       => 'Transfer to ' . $mineur->rib,
            'created_at'  => now()->subDays(5),
        ]);

        Transaction::create([
            'account_id'  => $mineur->id,
            'transfer_id' => $transfer2->id,
            'type'        => 'TRANSFER_IN',
            'amount'      => 500.00,
            'label'       => 'Transfer from ' . $courant1->rib,
            'created_at'  => now()->subDays(5),
        ]);

        Transaction::create([
            'account_id' => $courant1->id,
            'type'       => 'FEE',
            'amount'     => 50.00,
            'label'      => 'Monthly management fee',
            'created_at' => now()->subMonth()->startOfMonth(),
        ]);

        $interest = round($epargne->balance * (3.50 / 100 / 12), 2);
        Transaction::create([
            'account_id' => $epargne->id,
            'type'       => 'INTEREST',
            'amount'     => $interest,
            'label'      => 'Monthly interest (3.5% p.a.)',
            'created_at' => now()->subMonth()->startOfMonth(),
        ]);

        $interestMineur = round($mineur->balance * (2.00 / 100 / 12), 2);
        Transaction::create([
            'account_id' => $mineur->id,
            'type'       => 'INTEREST',
            'amount'     => $interestMineur,
            'label'      => 'Monthly interest (2% p.a.)',
            'created_at' => now()->subMonth()->startOfMonth(),
        ]);

        Transaction::create([
            'account_id' => $blocked->id,
            'type'       => 'FEE_FAILED',
            'amount'     => 50.00,
            'label'      => 'Monthly fee failed — insufficient balance',
            'created_at' => now()->startOfMonth(),
        ]);

        Transaction::create([
            'account_id' => $courant1->id,
            'type'       => 'DEBIT',
            'amount'     => 200.00,
            'label'      => 'ATM withdrawal',
            'created_at' => now()->subDays(3),
        ]);

        Transaction::create([
            'account_id' => $epargne->id,
            'type'       => 'CREDIT',
            'amount'     => 3000.00,
            'label'      => 'Salary deposit',
            'created_at' => now()->subDays(1),
        ]);
    }
}