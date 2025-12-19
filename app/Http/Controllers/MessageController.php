<?php
namespace App\Http\Controllers;

use App\Models\Message; 
use App\Models\Dossier; 
use App\Models\User; 
use App\Models\File; 
use App\Models\Folder; 
use App\Models\SoldeMensuel; 
use Illuminate\Http\Request;
 use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
   
    public function showForm()
    {
        return view('Charger-document');
    }

    public function sendEmail(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'email' => 'required|email',
            'message' => 'required|string|max:1000',
        ]);
        // dd($validated);
        Mail::raw($validated['message'], function ($message) use ($validated) {
            $message->to('compulog.services@gmail.com')
                    ->from($validated['email'])
                    ->subject('Nouveau message du formulaire de contact');
        });

        return redirect()->route('contact.form')->with('success', 'Message envoyé avec succès !');
    }

    public function markAsRead($messageId)
    {
        $message = Message::find($messageId);
    
        if ($message) {
            $message->is_read = true; // Marque le message comme lu
            $message->save();
    
            return response()->json(['success' => true, 'message' => 'Message marked as read']);
        }
    
        return response()->json(['success' => false, 'message' => 'Message not found'], 404);
    }
    // MessageController.php
public function updateStatus(Request $request, $messageId)
{

    $message = Message::find($messageId);

    if ($message) {
        $message->is_read = 1; // Marque le message comme lu
        $message->save();

        return response()->json(['success' => true, 'message' => 'Message marked as read']);
    }

    return response()->json(['success' => false, 'message' => 'Message not found'], 404);
}

 
public function store(Request $request)
{
    //  dd($request->all());
    $textMessage = $request->input('text_message');
    $commentaire = $request->input('commentaire'); // <-- récupère le commentaire séparé
    $userId = $request->input('user_id');
    $fileId = $request->input('file_id');
    $societeId = $request->input('societe_id');
    $folderId = $request->input('folder_id');
    $messageId = $request->input('reply_to_message_id');

    $message = Message::create([
        'text_message' => $textMessage,
        'commentaire' => $commentaire,
        'user_id' => $userId,
        'file_id' => $fileId,
        'societe_id' => $societeId,
        'folder_id' => $folderId,
        'parent_id' => $messageId,
    ]);
// $isFirstMessage = Message::where('file_id', $fileId)->count() === 1;
// // dd($textMessage);
// if($isFirstMessage ){
//  return back();
//  }else{
    return response()->json([
        'success' => true,
        'user_name' => auth()->user()->name,
        'created_at' => now()->format('Y-m-d H:i:s'),
        'text_message' => $textMessage,
        // 'isFirstMessage' => $isFirstMessage
    ]);
//  }
}

    public function getMessages($file_id, Request $request)
    {
         \Log::info('File ID:', ['file_id' => $file_id]);
    
        if (is_null($file_id)) {
            return response()->json(['success' => false, 'message' => 'File ID is required'], 400);
        }
    
        // Initialiser la requête
        $query = Message::with('user', 'replies.user')
                        ->where('file_id', $file_id)
                        ->whereNull('parent_id');
    
        // Filtrer par utilisateur si un user_id est fourni
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
    
        // Filtrer par date si une date est fournie
        if ($request->has('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }
    
        // Récupérer les messages
        $messages = $query->get();
    
        // Log le nombre de messages récupérés
        \Log::info('Messages retrieved:', ['count' => $messages->count()]);
    
        // Retourner les messages
        return response()->json([
            'messages' => $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'text_message' => $message->text_message,
                    'commentaire' => $message->commentaire,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user ? $message->user->name : 'Utilisateur inconnu',
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'is_read' => $message->is_read,
                    'replies' => $message->replies->map(function($reply) {
                        return [
                            'id' => $reply->id,
                            'text_message' => $reply->text_message,
                            'user_id' => $reply->user_id,
                            'user_name' => $reply->user ? $reply->user->name : 'Utilisateur inconnu',
                            'created_at' => $reply->created_at->format('Y-m-d H:i:s'),
                            'is_read' => $reply->is_read,
                        ];
                    }),
                ];
            })
        ]);
    }
    public function destroy($id)
    {
        // Trouver le message par son ID
        $message = Message::find($id);

        // Vérifier si le message existe
        if ($message) {
              $message->is_read = 0;
        $message->save();
            $message->delete();  // Supprimer le message de la base de données

            // Retourner une réponse JSON
            return response()->json(['success' => true]);
        }

        // Si le message n'est pas trouvé, retourner une erreur
        return response()->json(['success' => false, 'message' => 'Message non trouvé.']);
    }

    public function update($id, Request $request)
    {
        // Valider les données
        $request->validate([
            'text_message' => 'required|string|max:255',
        ]);

        // Trouver le message
        $message = Message::find($id);

        // Vérifier si le message existe
        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message introuvable'], 404);
        }

        // Vérifier si l'utilisateur est l'auteur du message
        if ($message->user_id !== auth()->user()->id) {
            return response()->json(['success' => false, 'message' => 'Vous ne pouvez pas modifier ce message'], 403);
        }

        // Mettre à jour le message
        $message->text_message = $request->text_message;
        $message->is_read = 0;
        $message->save();

        // Retourner une réponse JSON
        return response()->json(['success' => true, 'message' => 'Message modifié avec succès']);
    }
public function unreadMessages()
{
    try {
        $societeId = session('societeId');

        if (!$societeId) {
            return response()->json(['error' => 'Société non définie'], 400);
        }

        // Fonction pour filtrer les "non lus"
        $unreadCondition = function($query) {
            $query->where('is_read', 0)
                  ->orWhereNull('is_read')
                  ->orWhere('is_read', '');
        };

        // 1. Récupérer les messages non lus avec les fichiers et utilisateurs
        $messages = Message::where($unreadCondition)
            ->where('societe_id', $societeId)
            ->where('user_id', '!=', auth()->user()->id)
            ->with('file', 'user')
            ->latest()
            ->get();

        // 2. Extraire tous les types de fichiers liés aux messages
        $typesFromMessages = $messages->pluck('file.type')->filter()->unique()->values();

        // 3. Charger les dossiers correspondants à ces types
        $dossiersByName = Dossier::whereIn('name', $typesFromMessages)->get()->keyBy('name');

        // 4. Injecter le dossier dans chaque fichier lié au message
        foreach ($messages as $message) {
            if ($message->file) {
                $message->file->dossier = $dossiersByName[$message->file->type] ?? null;
            }
        }

        // Récupération des dossiers, soldes, fichiers, folders
        $dossiers = Dossier::with('user')
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->where('updated_by', '!=', auth()->user()->id)
            ->latest()
            ->get();

        $olddossiers = Dossier::with('user')
            ->onlyTrashed()
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->where('updated_by', '!=', auth()->id())
            ->latest()
            ->get();

        $renamedossiers = Dossier::with('user')
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->whereColumn('created_at', '!=', 'updated_at')
            ->where('updated_by', '!=', auth()->id())
            ->latest()
            ->get();

        $folders = Folder::with('updatedBy')
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->where('updated_by', '!=', auth()->id())
            ->latest()
            ->get();

        $oldfolders = Folder::with('updatedBy')
            ->onlyTrashed()
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->where('updated_by', '!=', auth()->id())
            ->latest()
            ->get();

        $renamefolders = Folder::with('updatedBy')
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->whereColumn('created_at', '!=', 'updated_at')
            ->where('updated_by', '!=', auth()->id())
            ->latest()
            ->get();

        $soldes = SoldeMensuel::with('updatedBy')
            ->where('societe_id', $societeId)
            ->where($unreadCondition)
            ->where('cloturer', true)
            ->where('updated_by', '!=', auth()->id())
            ->latest()
            ->get();

        $files = File::with(['societe', 'folder', 'updatedBy'])
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->where('updated_by', '!=', auth()->id())
            ->get();

        $renamefiles = File::with(['societe', 'folder', 'updatedBy'])
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->whereColumn('updated_at', '!=', 'created_at')
            ->where('updated_by', '!=', auth()->id())
            ->get();

        $oldfiles = File::with(['societe', 'folder', 'updatedBy'])
            ->onlyTrashed()
            ->where($unreadCondition)
            ->where('societe_id', $societeId)
            ->where('updated_by', '!=', auth()->id())
            ->get();

        // Injecter les dossiers dans les fichiers
        $types = $files->pluck('type')->unique()->filter()->values();
        $dossiers1 = Dossier::whereIn('name', $types)->get()->keyBy('name');

        foreach ($files as $file) {
            $file->dossier = $dossiers1[$file->type] ?? null;
        }

        return response()->json([
            'messages' => $messages,
            'dossiers' => $dossiers,
            'soldes'   => $soldes,
            'files'    => $files,
            'folders'  => $folders,
            'oldfiles' => $oldfiles,
            'oldfolders' => $oldfolders,
            'olddossiers' => $olddossiers,
            'renamefiles' => $renamefiles,
            'renamedossiers' => $renamedossiers,
            'renamefolders' => $renamefolders
        ]);

    } catch (\Exception $e) {
        \Log::error('Erreur unreadMessages: ' . $e->getMessage());
        return response()->json(['error' => 'Erreur serveur'], 500);
    }
}



}

