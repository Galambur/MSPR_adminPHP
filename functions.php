<?php
function getDataBase()
{
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=chateletdb;charset=utf8',
            'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    } catch (Exception $exception) {
        $bdd = null;
    }
    return $bdd;
}

function getConnexions(PDO $bdd, $fromTable, Array $cond = [], Array $condLike = [], $askSelect = '*', $specialCond = "")
{ //Cond pour Condition
    //Pour utiliser cette fonction il faut lui envoyer :
    //La bdd
    //Le(s) table au quel on veux accéder
    //Une liste des condtions à récupérer tel que :
    // array(arg1 => value1, arg2 => value 2, etc)
    //Il est possible de demander les conditions avec like aussi
    //Avec un exemple :
    // array( 'idClient' => 15, 'prenom' => 'Gaelle')
    $query = "SELECT {$askSelect} FROM {$fromTable} WHERE 1 ";
    //Etape 1 : On génère la requête sql avec les arguments demandés :
    foreach ($cond as $key => $arg) {
        $query = "{$query} AND {$key} = :p_{$key} ";
    }
    foreach ($condLike as $key2 => $arg2) {
        $query = "{$query} AND {$key2} LIKE :p_{$key2} ";
    }
    if (!empty($specialCond)) {
        $query = "{$query} AND {$specialCond}";
    }
    //Affectation des paramètres (Pour rappel les paramètres (p_arg) sont une sécurité)
    $statement = $bdd->prepare($query);
    foreach ($cond as $key => $arg) {
        $para = ':p_' . $key;
        $statement->bindValue($para, $arg);
    }
    foreach ($condLike as $key => $arg) {
        $arg = $arg . '%';
        $para = ':p_' . $key;
        $statement->bindValue($para, $arg);
    }
    //On réalise la requète et on renvoie le résultat
    $liste = null;
    if ($statement->execute()) {
        $liste = $statement->fetchALL(PDO::FETCH_OBJ);
        //On finie par fermer la ressource
    }
    $statement->closeCursor();
    return $liste;
}

function afficherErreur($erreur = null)
{
    if (!empty($erreur)) {
        $_SESSION["erreur"] = $erreur;
    }
    if (isset($_SESSION["erreur"])) {
        $valueErreur = $_SESSION["erreur"];
        if ($valueErreur == 1) {
            $erreur = 'Veuillez contacter l\'administrateur';
        } elseif ($valueErreur == 2) {
            $erreur = 'Mot de passe ou email incorrect';
        } elseif ($valueErreur == 3) {
            $erreur = 'Email incorrect';
        } elseif ($valueErreur == 4) {
            $erreur = 'Les mots de passe ne correspondent pas';
        } elseif ($valueErreur == 5) {
            $erreur = 'Email déjà utilisé';
        } elseif ($valueErreur == 6) {
            $erreur = 'Champ obligatoire incomplet';
        } elseif ($valueErreur == 7) {
            $erreur = 'Serveur introuvable!';
        } elseif ($valueErreur == 13) {
            $erreur = 'Vous devez être connecté';
        } else {
            $erreur = $_SESSION["erreur"];
        }

        unset($_SESSION["erreur"]);
    }
    if (isset($erreur)) {
        echo '
          <div class="erreur">
            <p>' . $erreur . '</p>
          </div>
          ';
    }
}

/*function getAllClients(PDO $bdd, $nomC) {
    $query = "SELECT p.nom AS nomPays, c.*
                FROM pays AS p, clients AS c WHERE p.id=c.pays_id";

// si on rentre pas quelque chose de vide, on rajoute ça
    if (! empty($nomC)) {
        $query .= " AND c.nom LIKE :c_nomC";
    }
    $clients = null;
    $statement = $bdd->prepare($query);

    // pour afficher toute la liste et pas juste un client
    if (! empty($nomC)) {
        $nomC = $nomC . '%';
        $statement->bindParam(':c_nomC', $nomC);
    }

    if ($statement->execute()) {
        $clients = $statement->fetchAll(PDO::FETCH_OBJ);
        // Fermeture de la ressource
        $statement->closeCursor();
    }
    return $clients;
}*/
?>
