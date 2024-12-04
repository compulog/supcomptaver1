<?php

namespace App\Http\Controllers;

use App\Models\OperationCourante;
use App\Models\PlanComptable;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class OperationCouranteController extends Controller
{
    public function index(Request $request)
    {
        $societe_id = session('societeId');  // Récupère l'ID de la société dans la session

        // Vérification si la requête est ajax
        if ($request->ajax()) {
            $query = OperationCourante::where('societe_id', $societe_id);

            // Filtrer par année et mois si demandés
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('date', $request->month);
            }

            return DataTables::of($query)
                ->addColumn('actions', function ($operation) {
                    return '<button class="btn btn-warning btn-sm editBtn" data-id="' . $operation->id . '">Modifier</button>
                            <button class="btn btn-danger btn-sm deleteBtn" data-id="' . $operation->id . '">Supprimer</button>';
                })
                ->addColumn('compte_select', function ($operation) {
                    // Retourne un select avec les comptes de la société
                    $planComptables = PlanComptable::where('societe_id', session('societeId'))->get();
                    $selectHtml = '<select class="form-control compte-select" data-id="'.$operation->id.'">';
                    foreach ($planComptables as $compte) {
                        $selected = $operation->compte == $compte->id ? 'selected' : '';
                        $selectHtml .= '<option value="'.$compte->id.'" '.$selected.'>'.$compte->intitule.'</option>';
                    }
                    $selectHtml .= '</select>';
                    return $selectHtml;
                })
                ->addColumn('date', function ($operation) {
                    return '<input type="date" class="form-control date-input" data-id="' . $operation->id . '" value="' . $operation->date->format('Y-m-d') . '">';
                })
                ->addColumn('libelle', function ($operation) {
                    return '<input type="text" class="form-control libelle-input" data-id="' . $operation->id . '" value="' . $operation->libelle . '">';
                })
                ->addColumn('numero_dossier', function ($operation) {
                    return '<input type="text" class="form-control dossier-input" data-id="' . $operation->id . '" value="' . $operation->numero_dossier . '">';
                })
                ->addColumn('numero_facture', function ($operation) {
                    return '<input type="text" class="form-control facture-input" data-id="' . $operation->id . '" value="' . $operation->numero_facture . '">';
                })
                ->rawColumns(['actions', 'compte_select', 'date', 'libelle', 'numero_dossier', 'numero_facture'])  // Permet l'édition inline dans les colonnes
                ->make(true);
        }

        // Charge la vue avec les plan_comptables
        return view('operation_courante');
    }

    // Met à jour tous les champs via AJAX
    public function updateField(Request $request, $id)
    {
        $operation = OperationCourante::findOrFail($id);

        // Mettre à jour le champ spécifié par l'utilisateur
        if ($request->has('field')) {
            $field = $request->field;
            $value = $request->value;

            if (in_array($field, ['date', 'numero_dossier', 'numero_facture', 'libelle', 'compte'])) {
                $operation->$field = $field == 'date' ? Carbon::parse($value) : $value;
                $operation->save();
            }
        }

        return response()->json(['success' => ucfirst($request->field) . ' mis à jour']);
    }

    // Ajouter une nouvelle opération via AJAX
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'numero_dossier' => 'required|string',
            'numero_facture' => 'required|string',
            'compte' => 'required|exists:plan_comptables,id',
            'libelle' => 'required|string',
        ]);

        $operation = OperationCourante::create([
            'date' => Carbon::parse($request->date),
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->compte,
            'libelle' => $request->libelle,
            'societe_id' => session('societeId'),
        ]);

        return response()->json(['success' => 'Opération ajoutée']);
    }

    // Suppression d'une opération
    public function destroy($id)
    {
        $operation = OperationCourante::findOrFail($id);
        $operation->delete();

        return response()->json(['success' => 'Opération supprimée']);
    }

    // Méthode pour récupérer les comptes
    public function getComptes()
    {
        $comptes = PlanComptable::where('societe_id', session('societeId'))->get();
        return response()->json(['comptes' => $comptes]);
    }
}
