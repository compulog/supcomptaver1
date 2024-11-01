<?php

namespace App\Http\Controllers;
use App\Models\PlanComptable;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
        // Afficher tous les journaux
        public function index()
        {
            return Journal::all(); 
        }

        public function getData()
        {
            $journaux = Journal::all();
            return response()->json($journaux);
        }
    
    
        public function getComptesAchats()
        {
            $comptes = PlanComptable::where(function($query) {
                $query->where('compte', 'LIKE', '21%')
                      ->orWhere('compte', 'LIKE', '22%')
                      ->orWhere('compte', 'LIKE', '23%')
                      ->orWhere('compte', 'LIKE', '24%')
                      ->orWhere('compte', 'LIKE', '25%')
                      ->orWhere('compte', 'LIKE', '613%')
                      ->orWhere('compte', 'LIKE', '611%')
                      ->orWhere('compte', 'LIKE', '614%')
                      ->orWhere('compte', 'LIKE', '618%')
                      ->orWhere('compte', 'LIKE', '631%')
                      ->orWhere('compte', 'LIKE', '612%');
            })
            ->get(['compte', 'intitule']);
    
            return response()->json($comptes);
        }
    
        public function getComptesVentes()
        {
            $comptes = PlanComptable::where(function($query) {
                $query->where('compte', 'LIKE', '711%')
                      ->orWhere('compte', 'LIKE', '712%')
                      ->orWhere('compte', 'LIKE', '718%')
                      ->orWhere('compte', 'LIKE', '732%')
                      ->orWhere('compte', 'LIKE', '738%')
                      ->orWhere('compte', 'LIKE', '733%');
            })
            ->get(['compte', 'intitule']);
    
            return response()->json($comptes);
        }
    
        public function getComptesTresoreries()
        {
            $comptes = PlanComptable::where('compte', 'LIKE', '514%')
            ->orWhere('compte', 'LIKE', '516%')
            ->orWhere('compte', 'LIKE', '554%')
                                    ->get(['compte', 'intitule']);
            return response()->json($comptes);
        }

        // Stocker un nouveau journal
        public function store(Request $request)
        {
            $request->validate([
                'code_journal' => 'required|string|max:255',
                'type_journal' => 'required|string|max:255',
                'intitule' => 'required|string|max:255',
                'contre_partie' => 'required|string|max:255',
            ]);
    
            Journal::create($request->all());
            return response()->json(['message' => 'Journal ajouté avec succès.']);
        }
    
        // Afficher un journal spécifique
        public function show($id)
        {
            return Journal::findOrFail($id);
        }
    
        // Mettre à jour un journal
        public function update(Request $request, $id)
        {
            $request->validate([
                'code_journal' => 'required|string|max:255',
                'type_journal' => 'required|string|max:255',
                'intitule' => 'required|string|max:255',
                'contre_partie' => 'required|string|max:255',
            ]);
    
            $journaux = Journal::findOrFail($id);
            $journaux->update($request->all());
            return response()->json(['message' => 'Journal mis à jour avec succès.']);
        }
    
        // Supprimer un journal
        public function destroy($id)
        {
            $journaux = Journal::findOrFail($id);
            $journaux->delete();
            return response()->json(['message' => 'Journal supprimé avec succès.']);
        }
}
