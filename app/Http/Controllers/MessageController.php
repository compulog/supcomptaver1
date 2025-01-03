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
         // Récupérer les données directement du formulaire
        $textMessage = $request->input('text_message');
        $userId = $request->input('user_id');
        $fileId = $request->input('file_id');
        $societeId = $request->input('societe_id');
        $folderId = $request->input('folder_id');

        // Création du message sans validation
        $message = Message::create([
            'text_message' => $textMessage,
            'user_id' => $userId,
            'file_id' => $fileId,
            'societe_id' => $societeId,
            'folder_id' => $folderId,
        ]);

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function getMessages(Request $request)
    {
        $fileId = $request->input('file_id');
        
        // Récupérer les messages associés à ce fichier avec le nom de l'utilisateur, où is_read est 0
        $messages = Message::with('user')
                            ->where('file_id', $fileId)
                            ->where('is_read', 0) // Filtrer les messages non lus (is_read = 0)
                            ->get();
       
        // Retourner les messages avec le nom de l'utilisateur
        return response()->json([
            'messages' => $messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'text_message' => $message->text_message,
                    'user_id' => $message->user_id,
                    'user_name' => $message->user ? $message->user->name : 'Utilisateur inconnu', // Nom de l'utilisateur
                ];
            })
        ]);
    }
    
    
}
