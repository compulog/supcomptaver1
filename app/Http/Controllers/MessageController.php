<?php
namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
   
  

    public function markAsRead($messageId)
    {
        // Trouver le message par son ID
        $message = Message::findOrFail($messageId);

        // Mettre à jour l'état 'is_read' du message
        $message->update(['is_read' => true]);

        // Retourner une réponse indiquant que l'opération a réussi
        return response()->json([
            'success' => true,
            'message' => 'Le message a été marqué comme lu.',
        ]);
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
    //    dd($request);
        $textMessage = $request->input('text_message');
        $userId = $request->input('user_id');
        $fileId = $request->input('file_id');
        $societeId = $request->input('societe_id');
        $folderId = $request->input('folder_id');
        $messageId = $request->input('reply_to_message_id');

        $message = Message::create([
            'text_message' => $textMessage,
            'user_id' => $userId,
            'file_id' => $fileId,
            'societe_id' => $societeId,
            'folder_id' => $folderId,   
            'parent_id' => $messageId,
        ]);

        return redirect()->route('achat.views', ['fileId' => $fileId]);
    }

    public function getMessages(Request $request)
    {
        $fileId = $request->input('file_id');
        
        // Récupérer les messages associés à ce fichier avec le nom de l'utilisateur, où is_read est 0
        $messages = Message::with('user', 'replies.user') // Charger les réponses et l'utilisateur
                            ->where('file_id', $fileId)
                            ->where('is_read', 0) // Filtrer les messages non lus
                            ->whereNull('parent_id') // Charger uniquement les messages sans parent (ceux qui sont originaux)
                            ->get();
    
        // Retourner les messages avec le nom de l'utilisateur, leurs réponses, ainsi que la date et l'heure
        return response()->json([
            'messages' => $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'text_message' => $message->text_message,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user ? $message->user->name : 'Utilisateur inconnu',
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'), // Ajouter la date et l'heure de création du message
                    'replies' => $message->replies->map(function($reply) {
                        return [
                            'id' => $reply->id,
                            'text_message' => $reply->text_message,
                            'user_id' => $reply->user_id,
                            'user_name' => $reply->user ? $reply->user->name : 'Utilisateur inconnu',
                            'created_at' => $reply->created_at->format('Y-m-d H:i:s'), // Ajouter la date et l'heure de création de la réponse
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
        $message->save();

        // Retourner une réponse JSON
        return response()->json(['success' => true, 'message' => 'Message modifié avec succès']);
    }
}
