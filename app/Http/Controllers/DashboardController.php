<?php
// FILE: app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'net_worth'     => 84230,
            'monthly_spend' => 12450,
            'savings_rate'  => 31.4,
            'idle_cash'     => 5200,
        ];

        $transactions = [
            ['icon'=>'🍜','name'=>'Lunch at Newari Kitchen', 'sub'=>'Today · Food',      'type'=>'debit',  'amount'=>320],
            ['icon'=>'💼','name'=>'Freelance Payment',        'sub'=>'Yesterday · Income', 'type'=>'credit', 'amount'=>8000],
            ['icon'=>'🚌','name'=>'Bus fare · Koteshwor',     'sub'=>'Yesterday · Transport','type'=>'debit', 'amount'=>25],
            ['icon'=>'📱','name'=>'Ncell Recharge',           'sub'=>'Feb 15 · Utilities', 'type'=>'debit',  'amount'=>500],
            ['icon'=>'🏦','name'=>'NMB Bank Transfer',        'sub'=>'Feb 14 · Banking',   'type'=>'credit', 'amount'=>15000],
        ];

        $ipos = [
            ['name'=>'Citizens Bank International','dates'=>'Feb 17 – Feb 19, 2026','status'=>'open',    'unit'=>100,'min'=>50],
            ['name'=>'Nabil Microfinance Ltd',      'dates'=>'Feb 24 – Feb 28, 2026','status'=>'upcoming','unit'=>100,'min'=>10],
            ['name'=>'Sanima DEPL Fund',             'dates'=>'Mar 3 – Mar 7, 2026',  'status'=>'upcoming','unit'=>10, 'min'=>100],
        ];

        $suggestions = [
            ['icon'=>'⚡','title'=>'Apply for Citizens IPO — closes in 2 days','body'=>'You have रू 5,200 idle. Min application रू 5,000 for 50 units. Closes Feb 19.','priority'=>'high'],
            ['icon'=>'🧠','title'=>'Food spending up 40% this month',           'body'=>'Consider meal prep 3x/week — estimated savings: रू 1,200/month.','priority'=>'medium'],
        ];

        $market = [
            'gold'      => 142500,
            'gold_chg'  => '+0.8%',
            'gold_up'   => true,
            'nepse'     => '2,148.62',
            'nepse_chg' => '-0.3%',
            'nepse_up'  => false,
            'usd_npr'   => '133.40',
            'usd_chg'   => '+0.1%',
            'usd_up'    => true,
        ];

        $chart = [
            'labels'  => ['Sep','Oct','Nov','Dec','Jan','Feb'],
            'income'  => [18000,22000,19500,28000,21000,18200],
            'expense' => [8200,11400,9800,13200,11800,12450],
        ];

        return view('dashboard', compact('stats','transactions','ipos','suggestions','market','chart'));
    }
}
