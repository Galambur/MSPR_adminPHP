<?php
session_start();
require('functions.php');
$bdd = getDataBase();

if(isset($_GET['id']) AND !empty($_GET['id']) AND isset($_GET['cle']) AND !empty($_GET['id'])) {
    $getid = $_GET['id'];
    $getcle = $_GET['cle'];
    $recupUser = $bdd->prepare('SELECT * FROM users WHERE id = :id AND cle = :cle');
    $recupUser->bindParam(':id', $_SESSION['id']);
    $recupUser->bindParam(':cle', $getcle);
    $recupUser->execute();
    if(!empty($recupUser)){
        $updateConfirmation = $bdd->prepare('UPDATE users SET confirme = 1 WHERE id = :id');
        $updateConfirmation->bindParam(':id', $getid);
        $updateConfirmation->execute();
        header('Location: connection.php');
    } else{
        echo "Votre clé ou votre identifiant est incorrect";
        }
    } else{
    echo "Aucun utilisateur trouvé";
}
?>