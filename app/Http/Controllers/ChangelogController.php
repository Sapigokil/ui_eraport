<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChangelogController extends Controller
{
    public function index()
    {
        // Mengambil data dari file config/app_history.php
        $history = config('app_history');

        return view('changelog', compact('history'));
    }
}