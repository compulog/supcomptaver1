<!DOCTYPE html>
<html>
<head>
    <title>Nouveau Message</title>
</head>
<body>
    <p><strong>{{ $sender->name }}</strong> a envoyé un nouveau message :</p>
    <p>{{ $message->text_message }}</p>
    @if ($message->commentaire)
        <p><em>Commentaire : {{ $message->commentaire }}</em></p>
    @endif
</body>
</html>
