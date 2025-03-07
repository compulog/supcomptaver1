<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Balance;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BalanceExport;
use Barryvdh\DomPDF\Facade as PDF;

class BalanceController extends Controller
{
    /**
     * Affiche la balance comptable avec possibilité de filtrer par compte et période.
     */
    public function index(Request $request)
    {
        $compte_debut  = $request->input('compte_debut');
        $compte_fin    = $request->input('compte_fin');
        $periode_debut = $request->input('periode_debut');
        $periode_fin   = $request->input('periode_fin');

        $query = Balance::query();

        if ($compte_debut) {
            $query->where('compte', '>=', $compte_debut);
        }
        if ($compte_fin) {
            $query->where('compte', '<=', $compte_fin);
        }
        if ($periode_debut) {
            $query->whereDate('date_operation', '>=', $periode_debut);
        }
        if ($periode_fin) {
            $query->whereDate('date_operation', '<=', $periode_fin);
        }

        $balanceData = $query->get();

        return view('Balance', compact('balanceData', 'compte_debut', 'compte_fin', 'periode_debut', 'periode_fin'));
    }

    /**
     * Export Excel via Maatwebsite/Excel.
     */
    public function exportExcel(Request $request)
    {
        $compte_debut  = $request->input('compte_debut');
        $compte_fin    = $request->input('compte_fin');
        $periode_debut = $request->input('periode_debut');
        $periode_fin   = $request->input('periode_fin');

        return Excel::download(new BalanceExport($compte_debut, $compte_fin, $periode_debut, $periode_fin), 'balance.xlsx');
    }

    /**
     * Export PDF via Dompdf.
     */
    public function exportPdf(Request $request)
    {
        $compte_debut  = $request->input('compte_debut');
        $compte_fin    = $request->input('compte_fin');
        $periode_debut = $request->input('periode_debut');
        $periode_fin   = $request->input('periode_fin');

        $query = Balance::query();

        if ($compte_debut) {
            $query->where('compte', '>=', $compte_debut);
        }
        if ($compte_fin) {
            $query->where('compte', '<=', $compte_fin);
        }
        if ($periode_debut) {
            $query->whereDate('date_operation', '>=', $periode_debut);
        }
        if ($periode_fin) {
            $query->whereDate('date_operation', '<=', $periode_fin);
        }
        $balanceData = $query->get();

        $pdf = PDF::loadView('balance.pdf', compact('balanceData'));
        return $pdf->download('balance.pdf');
    }
}
