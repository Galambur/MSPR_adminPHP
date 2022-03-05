<?php

require_once("head.php");

$bdd = getDatabase();

if (isset($bdd) AND !empty($_POST['email']) AND !empty($_POST['password'])) {
    $_SESSION["erreur"] = null;

    // todo : a modifier apres implementation de la recuperation d'adresse ip
    $connexionsId = getConnexions($bdd, 'connexions', Array("ip" => $_POST['ip']), Array());
    $ip="192.168.0.0";
    $navigateur = "test";

    if (!empty($connexionsId)) {
        if (count($connexionsId) >= 1) {
            $tentatives = $connexionsId[0]->nbTentatives;

            // on ajoute une tentative dans le nombre de tentatives
            $query = "UPDATE connexions SET nbTentatives = nbTentatives + 1 WHERE id=:id";
            $statement = $bdd->prepare($query);
            $statement->bindParam(':id', $connexionsId[0]->id);
            $statement->execute();

            // S'il y a eu moins de 10 identifications ratées dans la journée, on tente la connexion
            if ($tentatives < 10) {
                $connexions = getConnexions($bdd, 'connexions', Array("email" => $_POST['email'], "password" => $_POST['password']), Array());

                if (!empty($connexions)) {
                    if (count($connexions) == 1) {

                        if($connexions[0]->confirme != 1){
                            inscription($_POST['email'], $_POST['password'], $ip, $navigateur);
                            echo "Veuillez confirmer votre email puis vous connecter";
                        } else {
                            $id = $connexions[0]->id;
                            $_SESSION['id'] = $id;
                        }
                    } elseif (count($connexions) > 1) {
                        // Il existe plusieurs client avec la même adresse email
                        $_SESSION["erreur"] = 1;
                    } else {
                        // Le mot de passe ou l'email ne correspondent pas
                        $_SESSION["erreur"] = 2;
                    }
                } else {
                    // L'email n'est pas reconnu
                    $_SESSION["erreur"] = 3;
                }
            }
        }
    } else {
        // c'est la première fois qu'on essaie de se connecter avec cette IP, on incrémente le nb de tentatives
        // todo : implémenter l'adresse IP et le navigateur
        inscription($_POST['email'], $_POST['password'], $ip, $navigateur);
    }

    if($tentatives < 10){
        header('Location: index.php');
    } else {
        header('Location: locked.php');
    }
}
?>