<!-- <button id="scanBtn">Scanner un document</button>
<video id="camera" autoplay style="display:none;"></video>
<canvas id="canvas" style="display:none;"></canvas>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('scanBtn').addEventListener('click', async () => {
    const video = document.getElementById('camera');
    const canvas = document.getElementById('canvas');
    
    // Démarrer la caméra
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.style.display = 'block';

        // Attendre que la vidéo soit prête
        video.onloadedmetadata = () => {
            setTimeout(() => {
                // Capturer l'image après 2 secondes
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                // Arrêter la caméra
                stream.getTracks().forEach(track => track.stop());
                video.style.display = 'none';

                // Convertir en PDF
                const imgData = canvas.toDataURL('image/jpeg');
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF();
                pdf.addImage(imgData, 'JPEG', 10, 10, 190, 0); // taille auto
                pdf.save('scan.pdf');
            }, 2000);
        };
    } catch (err) {
        alert('Erreur d’accès à la caméra : ' + err.message);
    }
});
</script> -->



<!-- <button id="scanBtn">Scanner un document</button>
<button id="captureBtn" style="display:none;">Prendre la photo</button>
<video id="camera" autoplay style="display:none;"></video>
<canvas id="canvas" style="display:none;"></canvas>

 <input type="hidden" id="societe_id" value="{{ session()->get('societeId') }}">
<input type="hidden" id="folders" value="0">
<input type="hidden" id="type" value="achat">

 <meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.getElementById('scanBtn').addEventListener('click', async () => {
    const video = document.getElementById('camera');
    const captureBtn = document.getElementById('captureBtn');

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
        video.style.display = 'block';
        captureBtn.style.display = 'inline-block';

        captureBtn.onclick = async () => {
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Stop camera
            stream.getTracks().forEach(track => track.stop());
            video.style.display = 'none';
            captureBtn.style.display = 'none';

            // Generate PDF
            const imgData = canvas.toDataURL('image/jpeg');
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();
            pdf.addImage(imgData, 'JPEG', 10, 10, 190, 0);

            // Convert PDF to Blob
            const pdfBlob = pdf.output('blob');

            // Prepare form data
            const formData = new FormData();
            formData.append('file', pdfBlob, 'scan.pdf');
            formData.append('societe_id', document.getElementById('societe_id').value);
            formData.append('folders', document.getElementById('folders').value);
            formData.append('type', document.getElementById('type').value);

            // Send to server
            try {
                const response = await fetch("{{ route('uploadFile') }}", {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    alert('Fichier et données envoyés avec succès !');
                } else {
                    alert('Erreur lors de l’envoi : ' + response.statusText);
                }
            } catch (err) {
                alert('Erreur réseau : ' + err.message);
            }
        };
    } catch (err) {
        alert('Erreur d’accès à la caméra : ' + err.message);
    }
});
</script> -->


<!DOCTYPE html>
<html>
<head>
    <title>Envoyer un message</title>
</head>
<body>
    <h1>Envoyer un message</h1>

    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <form method="POST" action="{{ route('contact.send') }}">
        @csrf
        <label for="email">Votre Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label for="message">Votre Message:</label><br>
        <textarea name="message" rows="5" required></textarea><br><br>

        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
