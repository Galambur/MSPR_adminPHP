<?php

require_once("head.php");

$bdd = getDatabase();

// etape 1 : on vérifie que l'ip n'a pas tenté trop de connexions
if (isset($bdd) AND !empty($_POST['email']) AND !empty($_POST['password'])) {
    $_SESSION["erreur"] = null;
    $email = $_POST['email'];
    $password = $_POST['password'];

    // todo : a modifier apres implementation de la recuperation d'adresse ip

    $ip=getHostByName(getHostName());
    $navigateur = getNavigator();

    $connexionsId = getConnexions($bdd, 'connexions', Array("ip" => $ip), Array());

    $connexionsId = getConnexions($bdd, 'connexions', Array("ip" => $_POST['ip']), Array());

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
}


function connexion($bdd, $id, $email, $password, $ip, $nav){
    // vérification que le login / email existe sur l'AD
    if(!verifyAdLogin($email, $password)){
        $_SESSION["erreur"] = "Identifiants incorrects";

        // si le login / email n'existent pas, on rajoute 1 au nb de tentatives
        $query = "UPDATE connexions SET nbTentatives = nbTentatives + 1 WHERE ip=:ip";
        $statement = $bdd->prepare($query);
        $statement->bindParam(':ip', $ip);
        $statement->execute();

        header("index.php");
    } else {
        $connexions = getConnexions($bdd, 'connexions', Array("email" => $_POST['email'], "password" => $_POST['password']), Array());

        if (!empty($connexions)) {
            if (count($connexions) == 1) {
                $id = $connexions[0]->id;
                $_SESSION['id'] = $id;
                $_SESSION['erreur'] = "Connexion reussie";
                header("index.php");

                /*
                // envoie de l'email si le navigateur ou l'ip a changé
                // todo : implémenter
                $CURRENTip = "127.0.0.1";
                $CURRENTnav = "mozilla";
                $BDDip = $connexions[0]->ip;
                $BDDnav = $connexions[0]->navigateur;

                if($BDDip != $ip || $BDDnav != $nav){
                    $to      = $connexions[0]->email;
                    $subject = 'Changement de votre configuration d\'accès';
                    $message = 'Bonjour ! Nous avons remarqué que votre adresse IP ou votre navigateur
                         préféré avaient changés. Nous les avons donc mis à jour.';
                    $headers = 'From: mspradmin@epsi.fr' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

                    // todo: envoyer les mail
                    //mail($to, $subject, $message, $headers);

                    // changement du navigateur ou de l'ip si ils ont changé
                    if($BDDip != $ip){
                        $queryIP = "UPDATE connexions SET ip = :ip WHERE id=:id";
                        $statement = $bdd->prepare($queryIP);
                        $statement->bindParam(':ip', $CURRENTip);
                        $statement->bindParam(':id', $id);
                        $statement->execute();
                    } else if($BDDnav != $CURRENTnav) {
                        $queryNAV = "UPDATE connexions SET navigateur = navigateur WHERE id=:id";
                        $statement = $bdd->prepare($queryNAV);
                        $statement->bindParam(':navigateur', $CURRENTnav);
                        $statement->bindParam(':id',$id);
                        $statement->execute();
                    }
                }*/
            } elseif (count($connexions) > 1) {
                $_SESSION['id'] = $id;
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


function verifyAdLogin($email, $password){
    // connexion à l'active directory
    //parametres de connexion du serveur AD
    $serveur_ad_ip="192.168.208.128";
    $serveur_ad_port="389";

    //connection sur AD pour verifier login/mdp
    if(connection_ad($serveur_ad_ip, $serveur_ad_port, $email, $password)){
        return true;
    }
    else {
        return false;
    }
}


function connection_ad($ip, $port, $user, $pwd){
    $correct=false;
    $connection=ldap_connect($ip, $port) or die();
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

    if(!$connection){
        echo "Probleme de connection au serveur AD";
        exit;
    }
    else{
        $liaison = @ldap_bind($connection, $user, $pwd);
        if($liaison){
            $correct=true;
        }
    }
    ldap_close($connection);
    return $correct;
}
?>