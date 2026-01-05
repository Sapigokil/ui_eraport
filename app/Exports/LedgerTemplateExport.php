<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Http\Controllers\LedgerController;
use Illuminate\Http\Request;

class LedgerTemplateExport implements FromView
{
    private array $data;

    public function __construct(Request $request)
    {
        $this->data = app(LedgerController::class)
            ->buildLedgerData($request);
    }

    public function view(): View
    {
        return view('rapor.ledger_excel', $this->data);
    }
}
