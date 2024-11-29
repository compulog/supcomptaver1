<?php

namespace App\Http\Controllers;

use App\Models\OperationCourante;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;

class OperationCouranteController extends Controller
{
    // Appliquer le middleware pour vérifier que la société est disponible dans la session
    // public function __construct()
    // {
    //     $this->middleware('SetSocieteId');
    // }

    // Afficher la liste des opérations courantes avec filtrage par année, mois et société
    public function index(Request $request)
    {
        // Récupérer l'ID de la société depuis la session
        $societe_id = session('societeId');

        // Si la requête est Ajax, traiter les données et envoyer les résultats sous forme de JSON
        if ($request->ajax()) {
            $query = OperationCourante::where('societe_id', $societe_id);

            // Appliquer les filtres sur l'année et le mois
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }
            if ($request->filled('month')) {
                $query->whereMonth('date', $request->month);
            }

            // Utiliser DataTables pour gérer la pagination, le tri et le filtrage
            return DataTables::of($query)
                ->addColumn('actions', function($operation) {
                    return '<a href="' . route('operation_courante.edit', $operation->id) . '" class="btn btn-warning btn-sm">Modifier</a>
                            <button class="btn btn-danger btn-sm deleteBtn" data-id="' . $operation->id . '">Supprimer</button>';
                })
                ->rawColumns(['actions']) // Permet d'afficher du HTML dans la colonne actions
                ->make(true);
        }

        // Passer les données à la vue
        return view('operation_courante.index');
    }

    // Afficher le formulaire pour créer une nouvelle opération
    public function create()
    {
        return view('operation_courante.create');
    }

    // Enregistrer une nouvelle opération courante dans la base de données
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'date' => 'required|date',
            'numero_dossier' => 'required|string',
            'numero_facture' => 'required|string',
            'compte' => 'required|string',
            'libelle' => 'required|string',
            'debit' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'contre_partie' => 'nullable|string',
            'rubrique_tva' => 'nullable|string',
            'compte_tva' => 'nullable|string',
            'prorat_de_deduction' => 'nullable|numeric',
            'piece_justificative' => 'nullable|string',
            'type_journal' => 'required|string',
        ]);

        // Créer une nouvelle opération
        $operation = OperationCourante::create([
            'date' => Carbon::parse($request->date),
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->compte,
            'libelle' => $request->libelle,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
            'contre_partie' => $request->contre_partie,
            'rubrique_tva' => $request->rubrique_tva,
            'compte_tva' => $request->compte_tva,
            'prorat_de_deduction' => $request->prorat_de_deduction ?? 0,
            'piece_justificative' => $request->piece_justificative,
            'type_journal' => $request->type_journal,
            'societe_id' => session('societeId'),  // Utilisation du societe_id depuis la session
        ]);

        return redirect()->route('operation_courante.index')->with('success', 'Opération enregistrée avec succès.');
    }

    // Afficher le formulaire pour éditer une opération existante
    public function edit($id)
    {
        $operation = OperationCourante::findOrFail($id);
        return view('operation_courante.edit', compact('operation'));
    }

    // Mettre à jour les données d'une opération existante
    public function update(Request $request, $id)
    {
        // Validation des données
        $request->validate([
            'date' => 'required|date',
            'numero_dossier' => 'required|string',
            'numero_facture' => 'required|string',
            'compte' => 'required|string',
            'libelle' => 'required|string',
            'debit' => 'nullable|numeric',
            'credit' => 'nullable|numeric',
            'contre_partie' => 'nullable|string',
            'rubrique_tva' => 'nullable|string',
            'compte_tva' => 'nullable|string',
            'prorat_de_deduction' => 'nullable|numeric',
            'piece_justificative' => 'nullable|string',
            'type_journal' => 'required|string',
        ]);

        // Récupérer l'opération
        $operation = OperationCourante::findOrFail($id);

        // Mettre à jour les données
        $operation->update([
            'date' => Carbon::parse($request->date),
            'numero_dossier' => $request->numero_dossier,
            'numero_facture' => $request->numero_facture,
            'compte' => $request->compte,
            'libelle' => $request->libelle,
            'debit' => $request->debit ?? 0,
            'credit' => $request->credit ?? 0,
            'contre_partie' => $request->contre_partie,
            'rubrique_tva' => $request->rubrique_tva,
            'compte_tva' => $request->compte_tva,
            'prorat_de_deduction' => $request->prorat_de_deduction ?? 0,
            'piece_justificative' => $request->piece_justificative,
            'type_journal' => $request->type_journal,
            'societe_id' => session('societeId'),  // Utilisation du societe_id depuis la session
        ]);

        return redirect()->route('operation_courante.index')->with('success', 'Opération mise à jour avec succès.');
    }

    // Supprimer une opération
    public function destroy($id)
    {
        // Trouver l'opération et la supprimer
        $operation = OperationCourante::findOrFail($id);
        $operation->delete();

        return response()->json(['success' => 'Opération supprimée avec succès.']);
    }
}
