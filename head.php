<?php
session_start();

require_once( 'functions.php');
$id = null;
$_SESSION["erreur"] = null;
if (isset($_SESSION['id'])){
    $id = $_SESSION['id'];
}

$bdd = getDataBase();
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Le Chatelet</title>
</head>
<body>
<header>
    <div class="header">
        <h1>Le Chatelet</h1>
    </div>
</header>

<?php
    afficherErreur();
?>



