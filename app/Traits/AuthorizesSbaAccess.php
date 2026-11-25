<?php

namespace App\Traits;

use App\Models\Uji;

trait AuthorizesSbaAccess
{
    /**
     * Validasi hak akses terhadap suatu asesmen berdasarkan permission sba.view_all
     */
    protected function authorizeUjiAccess(Uji $uji): void
    {
        if (!auth()->user()->can('sba.view_all') && (int) $uji->user_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses asesmen milik pengguna lain.');
        }
    }
}