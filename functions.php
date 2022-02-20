<?php
function getDataBase()
{
    try {
        $bdd = new PDO('mysql:host=localhost;dbname=chateletdb;charset=utf8',
            'admin', 'admin', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    } catch (Exception $exception) {
        $bdd = null;
    }
    return $bdd;
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
