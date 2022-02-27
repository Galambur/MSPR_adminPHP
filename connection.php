<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=chateletdb;charset=utf8','root','');
if(isset($_POST['valider'])) {
    if (!empty($_POST['email'])) {
        $recupUser = $bdd->prepare('SELECT * FROM users WHERE email = ?');
        $recupUser->execute(array($_POST['email']));
        if ($recupUser->rowCount() > 0) {
            $userInfo = $recupUser->fetch();
            header('Location: verif.php?id='.$userInfo['id'].'&cle='.$userInfo['cle']);

    } else {
        echo "L'utilisateur n'existe pas";
    }

    }else{
        echo "Veulliez mettre votre e-mail";
    }
}
    ?>
<html>
<head>
    <title>Connexion</title>
    <meta charset="utf-8">

</head>
<body>
<form method="POST" actio="">
    <input type="email" name="email">
    <br>
    <input type="submit" name="valider">
</form>

</body>
</html>
