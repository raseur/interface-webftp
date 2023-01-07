<!doctype html>
<html>
	<?php 
	
	if(!isset($_GET["server"])){
		
	 
echo '<form method="post" action="ftp.php">';
echo ' <label for="server">Serveur :</label><br>';
echo '  <input type="text" id="server" name="server"><br>';
echo '  <label for="username">Nom d utilisateur :</label><br>';
echo '  <input type="text" id="username" name="username"><br>';
echo '  <label for="password">Mot de passe :</label><br>';
echo '  <input type="password" id="password" name="password"><br>';
echo '  <label for="port">Port :</label><br>';
echo '  <input type="number" id="port" name="port" value="21"><br>';
echo ' <label for="directory">Dossier :</label><br>';
echo '  <input type="text" id="directory" name="directory"><br>';
 echo ' <input type="submit" value="Envoyer">';
echo '</form>';	
	}
	
	?>
  <head>
    <title>Interface FTP</title>
    <style>
      table {
        border-collapse: collapse;
      }
      th, td {
        border: 1px solid black;
        padding: 5px;
      }
      th {
        background-color: lightgray;
      }
      a {
        color: blue;
      }
    </style>
  </head>
  <body>
    <h1>Interface FTP</h1>
    <?php
    // Récupération des valeurs du formulaire
    $ftp_server = isset($_POST["server"]) ? $_POST["server"] : $_GET["server"];
    $ftp_port = isset($_POST["port"]) ? $_POST["port"] : $_GET["port"];
    $ftp_username = isset($_POST["username"]) ? $_POST["username"] : $_GET["username"];
    $ftp_password = isset($_POST["password"]) ? $_POST["password"] : $_GET["password"];
    $directory = isset($_POST["directory"]) ? $_POST["directory"] : $_GET["directory"];

    // Connexion au serveur FTP
    $conn_id = ftp_connect($ftp_server, $ftp_port);
    ftp_login($conn_id, $ftp_username, $ftp_password);

    // Changement de dossier si spécifié
    if (!empty($directory)) {
      ftp_chdir($conn_id, $directory);
    }

    // Affichage de la liste des fichiers et dossiers
    echo "<table>";
    echo "<tr><th>Nom</th><th>Type</th><th>Taille</th><th>Action</th></tr>";

    $contents = ftp_nlist($conn_id, ".");
    foreach ($contents as $item) {
      $type = ftp_size($conn_id, $item) < 0 ? "Dossier" : "Fichier";
      $size = ftp_size($conn_id, $item);
      if ($type == "Dossier") {
        $link = "ftp.php?server=$ftp_server&username=$ftp_username&password=$ftp_password&port=$ftp_port&directory=$item";
        echo "<tr><td><a href='$link'>$item</a></td><td>$type</td><td>$size</td><td></td></tr>";
      } else {
        $download_link = "ftp.php?server=$ftp_server&username=$ftp_username&password=$ftp_password&port=$ftp_port&download_file=$item
";
    $view_link = "ftp.php?server=$ftp_server&username=$ftp_username&password=$ftp_password&port=$ftp_port&view_file=$item";
    $edit_link = "ftp.php?server=$ftp_server&username=$ftp_username&password=$ftp_password&port=$ftp_port&edit_file=$item";
    echo "<tr><td>$item</td><td>$type</td><td>$size</td>";
    echo "<td><a href='$download_link'>Télécharger</a> | <a href='$view_link'>Afficher</a> | <a href='$edit_link'>Éditer</a></td></tr>";
  }
}
	  $current_directory = ftp_pwd($conn_id);
if ($current_directory != "/") {
  $parent_directory = dirname($current_directory);
  $link = "ftp.php?server=$ftp_server&username=$ftp_username&password=$ftp_password&port=$ftp_port&directory=$parent_directory";
  echo "<a href='$link'>Revenir au dossier précédent</a>";
}
echo "</table>";

// Formulaire de téléversement de fichier
echo "<h2>Téléversement de fichier</h2>";
echo "<form action='ftp.php' method='post' enctype='multipart/form-data'>";
echo "Server: <input type='text' name='server' value='$ftp_server'><br>";
echo "Port: <input type='text' name='port' value='$ftp_port'><br>";
echo "Username: <input type='text' name='username' value='$ftp_username'><br>";
echo "Password: <input type='password' name='password' value='$ftp_password'><br>";
echo "Dossier: <input type='text' name='directory' value='$directory'><br>";
echo "Fichier: <input type='file' name='upload_file'><br>";
echo "<input type='submit' value='Téléverser'>";
echo "</form>";

// Affichage du contenu du fichier sélectionné
if (isset($_GET["view_file"])) {
  $view_file = $_GET["view_file"];
  $contents = ftp_get($conn_id, "php://temp", $view_file, FTP_ASCII);
  echo "<h2>Contenu du fichier $view_file</h2>";
  echo "<pre>$contents</pre>";
}

// Formulaire d'édition du fichier sélectionné
if (isset($_GET["edit_file"])) {
  $edit_file = $_GET["edit_file"];
  $contents = ftp_get($conn_id, "php://temp", $edit_file, FTP_ASCII);
  echo "<h2>Édition du fichier $edit_file</h2>";
  echo "<form action='ftp.php' method='post'>";
  echo "Server: <input type='text' name='server' value='$ftp_server'><br>";
  echo "Port: <input type='text' name='port' value='$ftp_port'><br>";
  echo "Username: <input type='text' name='username' value='$ftp_username'><br>";
  echo "Password: <input type='password' name='password' value='$ftp_password'><br>";
  echo "Dossier: <input type='text' name='directory' value='$directory'><br>";
  echo "Fichier: <input type='hidden' name='edit_file' value='$edit_file'>";
  echo "<textarea name='contents' rows='20' cols='80'>$contents</textarea><br>";
  echo "<input type='submit' value='Enregistrer'>";
  echo "</form>";
}

// Enregistrement des modifications au fichier sélectionné
if (isset($_POST["edit_file"])) {
  $edit_file = $_POST["edit_file"];
  $contents = $_POST["contents"];
  ftp_put($conn_id, $edit_file, "php://temp", FTP_ASCII);
  ftp_fput($conn_id, $edit_file, "php://temp", FTP_ASCII);
  echo "Fichier enregistré.";
}

// Téléchargement du fichier sélectionné
if (isset($_GET["download_file"])) {
  $download_file = $_GET["download_file"];
  header("Content-Disposition: attachment; filename=$download_file");
  ftp_get($conn_id, "php://output", $download_file, FTP_BINARY);
  exit;
}

ftp_close($conn_id);
?>
  </body>
</html>

