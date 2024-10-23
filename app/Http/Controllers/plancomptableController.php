<?php

namespace App\Http\Controllers;

use App\Models\PlanComptable;
use Illuminate\Http\Request;

class PlanComptableController extends Controller
{
   


    // Afficher la liste des comptes du plan comptable
    public function index()
    {
        $comptes = PlanComptable::all();
        return view('plan_comptable.index', compact('comptes'));
    }

    // Afficher le formulaire pour ajouter un nouveau compte
    public function create()
    {
        return view('plan_comptable.create');
    }

    // Ajouter un nouveau compte au plan comptable
    public function store(Request $request)
    {
        $request->validate([
            'compte' => 'required|string|unique:plan_comptable',
            'intitule' => 'required|string',
        ]);

        PlanComptable::create([
            'compte' => $request->compte,
            'intitule' => $request->intitule,
        ]);

        return redirect()->route('plan_comptable.index')->with('success', 'Compte ajouté avec succès.');
    }

    // Afficher le formulaire d'édition pour un compte spécifique
    public function edit($id)
    {
        $compte = PlanComptable::findOrFail($id);
        return view('plan_comptable.edit', compact('compte'));
    }

    // Mettre à jour un compte spécifique
    public function update(Request $request, $id)
    {
        $request->validate([
            'compte' => 'required|string',
            'intitule' => 'required|string',
        ]);

        return redirect()->route('plan_comptable.index')->with('success', 'Compte mis à jour avec succès.');
    }

    // Supprimer un compte spécifique
    public function destroy($id)
    {
        $compte = PlanComptable::findOrFail($id);
        $compte->delete();

        return redirect()->route('plan_comptable.index')->with('success', 'Compte supprimé avec succès.');
    }

    // use HasFactory;

    // protected $fillable = ['compte', 'intitule'];

    // // Relation inverse avec Fournisseur
    // public function fournisseurs()
    // {
    //     return $this->hasMany(Fournisseur::class);
    // }


}










