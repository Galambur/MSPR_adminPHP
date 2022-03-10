<?php

require_once("head.php");

$bdd = getDatabase();


// etape 1 : on verifie que l'ip n'a pas tente trop de connexions
if (isset($bdd) AND !empty($_POST['email']) AND !empty($_POST['password'])) {
    $_SESSION["erreur"] = null;
    $email = $_POST['email'];
    $password = $_POST['password'];

    //$ip = getHostByName(getHostName());
    $externalContent = file_get_contents("http://checkip.dyndns.com/");
    preg_match('/Current IP Address: \[?([:.0-9a-fA-F]+)\]?/', $externalContent, $m);
    $ip = $m[1];
    $country = getLocationInfoByIp($ip);
    $navigateur = getNavigator();

    // si l'adresse IP n'est pas française, on bloque la connexion
	//$country = 'EN';
    if($country != 'FR') {
        $_SESSION['erreur'] = "Vous devez avoir une IP Française";
        header("Location: notfrench.php");
        exit;
    }

    $connexionsId = getConnexions($bdd, 'connexions', Array("ip" => $ip), Array());

    if (!empty($connexionsId)) {
        if (count($connexionsId) >= 1) {
            $tentatives = $connexionsId[0]->nbTentatives;

            // S'il y a eu moins de 10 identifications ratees dans la journee, on tente la connexion
            if ($tentatives < 10) {
                $connexions = getConnexions($bdd, 'connexions', Array("email" => $_POST['email'], "password" => $_POST['password']), Array());

                if (!empty($connexions)) {
                    if (count($connexions) == 1) {

                        if ($connexions[0]->confirme != 1) {
                            inscription($_POST['email'], $_POST['password'], $ip, $navigateur);
                            $_SESSION['erreur'] = "Veuillez confirmer votre email puis vous connecter";
                        } else if (($connexions[0]->confirme == 1)) {
                            $id = $connexions[0]->id;
                            $result = connexion($bdd, $id, $email, $password, $ip, $navigateur);
                            if (!$result) {
                                // si le login / email n'existent pas, on rajoute 1 au nb de tentatives
                                addEssaie($bdd, $ip);
                                //header("Location: index.php");
                            } else {
                                $_SESSION['erreur'] = "Connecte correctement";
                                header("Location: connecte.php");
                            }
                        }
                    } elseif (count($connexions) > 1) {
                        // Il existe plusieurs client avec la même adresse email
                        $_SESSION["erreur"] = "Plusieurs adresses mail trouvees";
                        //header("Location: index.php");
                    } else {
                        // Le mot de passe ou l'email ne correspondent pas
                        $_SESSION["erreur"] = "Mot de passe ou email incorrects";
                        addEssaie($bdd, $ip);
                        //header("Location: index.php");
                    }
                } else {
                    // L'email n'est pas reconnu
                    $_SESSION["erreur"] = "Email non reconnu";
                    addEssaie($bdd, $ip);
                    //header("Location: index.php");
                }
            } else {
                var_dump("trop d'essais");
                //header("Location: locked.php");
            }
        }
    } else {
        // c'est la première fois qu'on essaie de se connecter avec cette IP
        inscription($_POST['email'], $_POST['password'], $ip, $navigateur);
        header("Location: index.php");
    }
}

function addEssaie($bdd, $ip)
{
    $query = "UPDATE connexions SET nbTentatives = nbTentatives + 1 WHERE ip=:ip";
    $statement = $bdd->prepare($query);
    $statement->bindParam(':ip', $ip);
    $statement->execute();
}


function connexion($bdd, $id, $email, $password, $ip, $nav)
{
// verification que le login / email existe sur l'AD
    if (!verifyAdLogin($email, $password)) {
        $_SESSION["erreur"] = "Identifiants AD incorrects";
        return false;
    } else {
        $connexions = getConnexions($bdd, 'connexions', Array("email" => $_POST['email'], "password" => $_POST['password']), Array());

        if (!empty($connexions)) {
            if (count($connexions) == 1) {
                $id = $connexions[0]->id;
                $_SESSION['id'] = $id;

                // envoie de l'email si le navigateur ou l'ip a change
                $CURRENTip = getHostByName(getHostName());
                $CURRENTnav = getNavigator();
                $BDDip = $connexions[0]->ip;
                $BDDnav = $connexions[0]->navigateur;

                if ($BDDip != $ip || $BDDnav != $nav) {

                    // todo : remettre le bon mail
                    $to = "gaelle.derambure@epsi.fr";
                    $name = 'Le Chatelet';
                    $subj = 'Changement de votre configuration d\'acces';
                    $from = 'clinique@chatelet.local';
                    $msg = 'Bonjour ! Nous avons remarque que votre adresse IP ou votre navigateur
                         prefere avaient changes. Nous les avons donc mis a jour.';

                    smtpmailer($to, $from, $name, $subj, $msg);

                    // changement du navigateur ou de l'ip si ils ont change
                    if ($BDDip != $ip) {
                        $queryIP = "UPDATE connexions SET ip = :ip WHERE id=:id";
                        $statement = $bdd->prepare($queryIP);
                        $statement->bindParam(':ip', $CURRENTip);
                        $statement->bindParam(':id', $id);
                        $statement->execute();
                    } else if ($BDDnav != $CURRENTnav) {
                        $queryNAV = "UPDATE connexions SET navigateur = :navigateur WHERE id=:id";
                        $statement = $bdd->prepare($queryNAV);
                        $statement->bindParam(':navigateur', $CURRENTnav);
                        $statement->bindParam(':id', $id);
                        $statement->execute();
                    }
                }
                $_SESSION['erreur'] = "Correctement connecte";
                header("Location: index.php");
                return true;
            }
        }
    }
    return false;
}


function verifyAdLogin($email, $password)
{
    // connexion à l'active directory
    //parametres de connexion du serveur AD
    $serveur_ad_ip = "192.168.208.128";
    $serveur_ad_port = "389";

    //connection sur AD pour verifier login/mdp
    if (connection_ad($serveur_ad_ip, $serveur_ad_port, $email, $password)) {
        return true;
    } else {
        return false;
    }
}


function connection_ad($ip, $port, $user, $pwd)
{
    $correct = false;
    $connection = ldap_connect($ip, $port) or die();
    ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);

    if (!$connection) {
        $_SESSION["erreur"] = "Probleme de connection au serveur AD";
        exit;
    } else {
        $liaison = @ldap_bind($connection, $user, $pwd);
        if ($liaison) {
            $correct = true;
        }
    }
    ldap_close($connection);
    return $correct;
}

?>

<html>

<?php
echo $_SESSION["erreur"];
?>

</html>