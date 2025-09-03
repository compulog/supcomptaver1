
 <!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>SUPCOMPTA BY COMPULOG</title>
   <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .image-container {
     margin-left:-20%;
    }

    .image-container img {
    
      display: block;
    }

@font-face {
  font-family: 'Gagalin';
  src: url('fonts/gagalin.woff2') format('woff2'),
       url('fonts/gagalin.woff') format('woff'),
       url('fonts/gagalin.ttf') format('truetype');
  font-weight: normal;
  font-style: normal;
}
.image-container .text-on-image {
  position: absolute;
  top: 75%;
  left: 28%;
  transform: translate(-50%, -50%);
  color: #004aad;
  font-size: 18px;       /* Taille plus grande */
  font-weight: normal;   /* Gagalin est souvent fine, pas forcément bold */
  font-style: normal;    /* ou italic si tu veux */
  font-family: 'Gagalin', cursive, sans-serif;
  padding: 10px 15px;
  border-radius: 10px;
}


    .form-section {
      padding: 40px;
      box-sizing: border-box;
    }

    .form-section h1 {
      color: rgb(1, 72, 173);
    }

    .form-section h4 {
      color: rgb(167, 167, 192);
    }

    .form-section p {
      color: rgb(174, 174, 177);
      font-size: 13px;
    }

    .form-section input[type="text"],
    .form-section input[type="password"] {
      width: 95%;
      padding: 12px;
      margin: 10px 0 20px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .form-section button {
      width: 30%;
      padding: 12px;
      background-color: rgb(1, 72, 173);
      color: white;
      border: none;
      border-radius: 25px;
      cursor: pointer;
      font-size: 16px;
    }

    .form-section button:hover {
      background-color: rgb(19, 6, 133);
    }
    select{
       width: 100%;
       height: 40px;
       color:rgb(174, 174, 177);
       border-color:rgb(174, 174, 177);
    }
  </style>
  <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">

</head>
<body>
      <img src="../assets/img/curved-images/SUPCOMPTALogoFinal.png" alt="Image" style="width:18%;margin-top:-50%;margin-left:-50px;">

<div id="loader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: white; display: flex; justify-content: center; align-items: center; z-index: 9999;">

    <video autoplay muted loop style="max-width: 50%; max-height: 50%; object-fit: contain;">

        <source src="{{ asset('vidio/SupcomptaAnimation.mp4') }}" type="video/mp4">

        Votre navigateur ne prend pas en charge la vidéo.

    </video>

</div>





  <div class="image-container">
    <img src="../assets/img/curved-images/template 001.png" alt="Image">
      <div class="text-on-image">
     TELE-DÉCLARATION COMPTABILITÉ PAIE
    </div>
  </div>

  <div class="form-section">
    <h1>Connexion</h1>
    <h4>Bienvenue sur votre espace sécurisé.</h4>
    <p>
      Veuillez vous connecter pour continuer. Vos informations sont protégées <br>
      et utilisées uniquement pour vous offrir une expérience personnalisée.
    </p>
<form role="form" method="POST" action="/session" id="loginForm">
  @csrf
                      <input type="text" class="form-control" name="name" id="name"  placeholder="✉ Nom d'utilisateur"value="" aria-label="name" aria-describedby="name-addon">

                      <input type="password" class="form-control" name="password" id="password" placeholder="⚿ Mot de passe" value="secret" aria-label="Password" aria-describedby="password-addon">

  <div class="mb-3" id="databaseSelectContainer" style="display: none;">

                      <select name="database" class="form-control" id="databaseSelect">

                        

                      <option value="" >choisire une option</option>

                   
                        @foreach ($dbNames as $dbName)
                            @if ($dbName != 'compulo2_compta_auth')
                                <option value="{{ $dbName }}">{{ $dbName }}</option>
                            @endif
                        @endforeach

                      </select>

                    </div>

      <p>
        <input class="form-check-input" type="checkbox" id="rememberMe" checked style="background-color:#568BAC;">
        Souviens-toi de moi
      </p>
      <br>
      <button type="submit" class="btn">Connexion</button>
    </form>
  </div>



<script>

  document.addEventListener('DOMContentLoaded', function () {

    // Attendre 3 secondes (1000 ms) avant de masquer l'animation et afficher la page de login

    setTimeout(function() {

      document.getElementById('loader').style.display = 'none';  // Cacher l'animation

      document.getElementById('loginPage').style.display = 'block';  // Afficher la page de login

    }, 2000);  // Délai de 1 secondes

  });

</script>

<script>

  document.addEventListener('DOMContentLoaded', function () {

    const nameInput = document.getElementById('name');

    const passwordInput = document.getElementById('password');

    const databaseSelectContainer = document.getElementById('databaseSelectContainer');

    

    // Function to check credentials

    function checkCredentials() {

      const name = nameInput.value;

      const password = passwordInput.value;

      

      // Check if the name and password match the correct values

      if (name === 'COMPULOG_CHABAANE' && password === 'compulog123') {

        // Show the select element if credentials are correct

        databaseSelectContainer.style.display = 'block';

      } else {

        // Hide the select element if credentials are incorrect

        databaseSelectContainer.style.display = 'none';

      }

    }



    // Listen to changes in the name and password fields

    nameInput.addEventListener('input', checkCredentials);

    passwordInput.addEventListener('input', checkCredentials);

    

    // Initial check in case the user has pre-filled the fields

    checkCredentials();

  });

</script>


 


</body>
</html>
 