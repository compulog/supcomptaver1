<?php

namespace App\Http\Controllers;
use App\Models\PlanComptable;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class JournalController extends Controller
{
    public function checkPassword(Request $request)
    {
        // Valider que le mot de passe est bien présent
        $request->validate([
            'password' => 'required|string',
        ]);

        // Récupérer l'utilisateur actuellement connecté
        $user = Auth::user();

        // Vérifier si le mot de passe correspond à celui de l'utilisateur
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false], 401); // Mot de passe incorrect
        }
    }

        // Afficher tous les journaux
        public function index()
        {
           // Récupérer l'ID de la société dans la session
    $societeId = session('societeId');
    
    // Vérifier si l'ID de la société existe dans la session
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // Récupérer tous les journaux qui appartiennent à la société spécifiée
    $journaux = Journal::where('societe_id', $societeId)->get();

    // Retourner les journaux associés à la société en format JSON
    return response()->json($journaux);
        }

        public function getData()
        {
            
    // Récupérer l'ID de la société dans la session
    $societeId = session('societeId');
    
    // Vérifier si l'ID de la société existe dans la session
    if (!$societeId) {
        return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
    }

    // Récupérer tous les journaux qui appartiennent à la société spécifiée
    $journaux = Journal::where('societe_id', $societeId)->get();

    // Retourner les journaux associés à la société en format JSON
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
            // Récupérer l'ID de la société depuis la session
            $societeId = session('societeId');
            
            // Vérifier si l'ID de la société existe dans la session
            if (!$societeId) {
                return response()->json(['error' => 'Aucune société sélectionnée dans la session'], 400);
            }
    
            // Validation des données
            $validatedData = $request->validate([
                'code_journal' => 'required|string|max:255',
                'type_journal' => 'required|string|max:255',
                'intitule' => 'required|string|max:255',
                'contre_partie' => 'nullable|string|max:255',
            ]);
    
            // Ajouter l'ID de la société au journal
            $validatedData['societe_id'] = $societeId;
    
            // Créer un nouveau journal
            Journal::create($validatedData);
    
            // Retourner une réponse JSON avec un message de succès
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
                'type_journal' => 'nullable|string|max:255',
                'intitule' => 'nullable|string|max:255',
                'contre_partie' => 'nullable|string|max:255',
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

        public function deleteSelected(Request $request)
        {
            // Valider que le tableau 'ids' est bien fourni
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer',  // Chaque ID doit être un entier
            ]);
        
            try {
                // Supprimer les lignes avec les IDs reçus
                $deletedCount = journal::whereIn('id', $request->ids)->delete();
        
                return response()->json([
                    'status' => 'success',
                    'message' => "{$deletedCount} lignes supprimées"
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Erreur lors de la suppression.',
                    'error' => $e->getMessage()  // Retour de l'erreur spécifique
                ]);
            }
        }



}
