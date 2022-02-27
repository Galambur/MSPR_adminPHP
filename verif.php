<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=chateletdb;charset=utf8', 'root', '');
if(isset($_GET['id']) AND !empty($_GET['id']) AND isset($_GET['cle']) AND !empty($_GET['id'])) {
    $getid = $_GET['id'];
    $getcle = $_GET['cle'];
    $recupUser = $bdd->prepare('SELECT * FROM users WHERE id = ? AND cle = ?');
    $recupUser->execute(array($getid, $getcle));
    if($recupUser->rowCount() > 0){
        $userInfo = $recupUser->fetch();
        if($userInfo['confirme'] != 1){
            $updateConfirmation = $bdd->prepare('UPDATE users SET confirme = ? WHERE id = ?');

        }else{
            $_SESSION['cle'] = $getcle;
            header('Location: connection.php');
        }
    }else{
        echo "Votre clé ou votre identifiant est incorrect";
        }
    }else{
    echo "Aucun utilisateur trouvé";
}
?>