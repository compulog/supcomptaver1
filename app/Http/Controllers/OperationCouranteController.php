<?php

namespace App\Http\Controllers;

use App\Models\OperationCourante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class OperationCouranteController extends Controller
{
    protected $societe_id;

    public function __construct()
    {
        // Récupérer l'id de la société de la session pour le partager dans toutes les méthodes
        $this->societe_id = session('societeId');
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = OperationCourante::where('societe_id', $this->societe_id);

            // Applique les filtres année et mois si spécifiés dans la requête
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('date', $request->month);
            }

            // Retourne les données au format JSON pour DataTables
            return DataTables::of($query)
                ->addColumn('actions', function ($operation) {
                    return '<button class="btn btn-warning btn-sm editBtn" data-id="' . $operation->id . '">Modifier</button>
                            <button class="btn btn-danger btn-sm deleteBtn" data-id="' . $operation->id . '">Supprimer</button>';
                })
                ->addColumn('date', function ($operation) {
                    return '<input type="date" class="form-control date-input" data-id="' . $operation->id . '" value="' . $operation->date->format('Y-m-d') . '">';
                })
                ->addColumn('numero_dossier', function ($operation) {
                    return '<input type="text" class="form-control dossier-input" data-id="' . $operation->id . '" value="' . $operation->numero_dossier . '">';
                })
                ->addColumn('numero_facture', function ($operation) {
                    return '<input type="text" class="form-control facture-input" data-id="' . $operation->id . '" value="' . $operation->numero_facture . '">';
                })
                ->addColumn('compte', function ($operation) {
                    return '<input type="text" class="form-control compte-input" data-id="' . $operation->id . '" value="' . $operation->compte . '">';
                })
                ->addColumn('libelle', function ($operation) {
                    return '<input type="text" class="form-control libelle-input" data-id="' . $operation->id . '" value="' . $operation->libelle . '">';
                })
                ->addColumn('debit', function ($operation) {
                    return '<input type="text" class="form-control debit-input" data-id="' . $operation->id . '" value="' . $operation->debit . '">';
                })
                ->addColumn('credit', function ($operation) {
                    return '<input type="text" class="form-control credit-input" data-id="' . $operation->id . '" value="' . $operation->credit . '">';
                })
                ->addColumn('contre_partie', function ($operation) {
                    return '<input type="text" class="form-control contre_partie-input" data-id="' . $operation->id . '" value="' . $operation->contre_partie . '">';
                })
                ->addColumn('rubrique_tva', function ($operation) {
                    return '<input type="text" class="form-control rubrique_tva-input" data-id="' . $operation->id . '" value="' . $operation->rubrique_tva . '">';
                })
                ->addColumn('compte_tva', function ($operation) {
                    return '<input type="text" class="form-control compte_tva-input" data-id="' . $operation->id . '" value="' . $operation->compte_tva . '">';
                })
                ->addColumn('prorat_de_deduction', function ($operation) {
                    return '<input type="text" class="form-control prorat_de_deduction-input" data-id="' . $operation->id . '" value="' . $operation->prorat_de_deduction . '">';
                })
                ->addColumn('piece_justificative', function ($operation) {
                    return '<input type="text" class="form-control piece_justificative-input" data-id="' . $operation->id . '" value="' . $operation->piece_justificative . '">';
                })
                ->addColumn('type_journal', function ($operation) {
                    return '<input type="text" class="form-control type_journal-input" data-id="' . $operation->id . '" value="' . $operation->type_journal . '">';
                })
                ->rawColumns([
                    'actions', 'date', 'numero_dossier', 'numero_facture', 'compte', 'libelle',
                    'debit', 'credit', 'contre_partie', 'rubrique_tva', 'compte_tva', 'prorat_de_deduction', 'piece_justificative', 'type_journal'
                ])
                ->make(true);  // Retourne la réponse JSON attendue par DataTables
        }

        // Retourner la vue avec les comptes
        return view('operation_courante');
    }

    // Met à jour tous les champs via AJAX
    public function updateField(Request $request, $id)
    {
        $operation = OperationCourante::where('id', $id)->where('societe_id', $this->societe_id)->first();

        if (!$operation) {
            return response()->json(['error' => 'Opération non trouvée ou société non autorisée'], 404);
        }

        // Mettre à jour le champ spécifié par l'utilisateur
        if ($request->has('field')) {
            $field = $request->field;
            $value = $request->value;

            if (in_array($field, ['date', 'numero_dossier', 'numero_facture', 'libelle', 'compte', 'debit', 'credit', 'contre_partie', 'rubrique_tva', 'compte_tva', 'prorat_de_deduction', 'piece_justificative', 'type_journal'])) {
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
            'compte' => 'required|string',
            'libelle' => 'required|string',
            'debit' => 'required|numeric',
            'credit' => 'required|numeric',
            'contre_partie' => 'required|string',
            'rubrique_tva' => 'nullable|string',
            'compte_tva' => 'nullable|string',
            'prorat_de_deduction' => 'nullable|numeric',
            'piece_justificative' => 'nullable|string',
            'type_journal' => 'nullable|string',
        ]);

        $operation = OperationCourante::create([
            'date' => Carbon::parse($request->date),
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->compte,
            'libelle' => $request->libelle,
            'debit' => $request->debit,
            'credit' => $request->credit,
            'contre_partie' => $request->contre_partie,
            'rubrique_tva' => $request->rubrique_tva,
            'compte_tva' => $request->compte_tva,
            'prorat_de_deduction' => $request->prorat_de_deduction,
            'piece_justificative' => $request->piece_justificative,
            'type_journal' => $request->type_journal,
            'societe_id' => $this->societe_id,  // Utiliser le societe_id de la session
        ]);

        return response()->json(['success' => 'Opération ajoutée']);
    }

    // Suppression d'une opération
    public function destroy($id)
    {
        $operation = OperationCourante::where('id', $id)->where('societe_id', $this->societe_id)->first();

        if (!$operation) {
            return response()->json(['error' => 'Opération non trouvée ou société non autorisée'], 404);
        }

        $operation->delete();

        return response()->json(['success' => 'Opération supprimée']);
    }
}
