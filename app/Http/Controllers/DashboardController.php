<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    /**
     * Show the authenticated user's dashboard.
     */
    public function __invoke(): View
    {
        return view('dashboard');
    }
}