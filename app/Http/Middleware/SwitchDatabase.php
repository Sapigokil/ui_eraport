<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SwitchDatabase
{
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah session 'mode_simulasi' sedang menyala (true)
        if (session('mode_simulasi') === true) {
            // Paksa Laravel menggunakan koneksi mysql_simulasi
            Config::set('database.default', 'mysql_simulasi');
            
            // Bersihkan cache koneksi agar perpindahan berjalan mulus
            DB::purge('mysql');
            DB::purge('mysql_simulasi');
            DB::reconnect('mysql_simulasi');
        }

        return $next($request);
    }
}