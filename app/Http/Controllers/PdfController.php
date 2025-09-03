<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Smalot\PdfParser\Parser; 
use OpenAI\Client as OpenAiClient;
 class PdfController extends Controller
{

public function upload(Request $request)
{
    $request->validate([
        'pdf' => 'required|file|mimes:pdf|max:10000',
    ]);

    $file = $request->file('pdf');

    // Initialiser le parser
    $parser = new Parser();

    // Lire le contenu du fichier PDF
    $pdf = $parser->parseFile($file->getPathname());

    // Extraire le texte
    $text = $pdf->getText();

    // Afficher ou enregistrer le texte
    return response()->json([
        'extrait' => $text
    ]);
}
    protected function extractTablesWithAI(string $text)
{
  /** @var Client $openai */
  $openai = app(Client::class);

  // On chunk le texte si très long, ou on utilise directement
  $response = $openai->chat()->create([
    'model'    => 'gpt-4',
    'messages' => [
      ['role'=>'system','content'=>"
        Tu es un extracteur de tableaux depuis un PDF. 
        Transforme le texte suivant en JSON array d’objets, 
        où chaque objet représente une ligne de table avec clés nommées selon les colonnes.
      "],
      ['role'=>'user','content'=>substr($text,0,6000)], 
    ],
    'temperature' => 0.0,
  ]);

  $json = $response->choices[0]->message->content;

  // On vérifie que c’est du JSON valide
  $data = json_decode($json, true) 
       ?? throw new \Exception("Réponse IA non-JSON");

  // Sauvegarde en BD ou on renvoie directement
  return response()->json($data);
}

public function rows(PdfModel $pdf)
{
  $rows = $pdf->rows()->get(); // Eloquent relationship
  return response()->json($rows);
}

}