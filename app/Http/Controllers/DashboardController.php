<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalDrivers = Driver::query()->count();

        return view('dashboard', compact('totalDrivers'));
    }
} 