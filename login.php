<?php

require_once("head.php");

$bdd = getDatabase();

// etape 1 : on vérifie que l'ip n'a pas tenté trop de connexions
if (isset($bdd) AND !empty($_POST['email']) AND !empty($_POST['password'])) {
    $_SESSION["erreur"] = null;
    $email = $_POST['email'];
    $password = $_POST['password'];

    $ip = getHostByName(getHostName());
    $navigateur = getNavigator();

    $connexionsId = getConnexions($bdd, 'connexions', Array("ip" => $ip), Array());

    if (!empty($connexionsId)) {
        if (count($connexionsId) >= 1) {
            $tentatives = $connexionsId[0]->nbTentatives;

            // S'il y a eu moins de 10 identifications ratées dans la journée, on tente la connexion
            if ($tentatives < 10) {
                $connexions = getConnexions($bdd, 'connexions', Array("email" => $_POST['email'], "password" => $_POST['password']), Array());

                if (!empty($connexions)) {
                    if (count($connexions) == 1) {

                        if ($connexions[0]->confirme != 1) {
                            inscription($_POST['email'], $_POST['password'], $ip, $navigateur);
                            $_SESSION['erreur'] = "Veuillez confirmer votre email puis vous connecter";
                        } else if(($connexions[0]->confirme == 1)) {
                            $id = $connexions[0]->id;
                            $result = connexion($bdd, $id, $email, $password, $ip, $navigateur);
							if(!$result) {
								// si le login / email n'existent pas, on rajoute 1 au nb de tentatives
								$query = "UPDATE connexions SET nbTentatives = nbTentatives + 1 WHERE ip=:ip";
								$statement = $bdd->prepare($query);
								$statement->bindParam(':ip', $ip);
								$statement->execute();
							}
                            header("index.php");
                        }
                    } elseif (count($connexions) > 1) {
                        // Il existe plusieurs client avec la même adresse email
                        $_SESSION["erreur"] = "Plusieurs adresses mail trouvées";
                    } else {
                        // Le mot de passe ou l'email ne correspondent pas
                        $_SESSION["erreur"] = "Mot de passe ou email incorrects";
                    }
                } else {
                    // L'email n'est pas reconnu
                    $_SESSION["erreur"] = "Email non reconnu";
                }
            } else {
				header("locked.php");
			}
        }
    } else {
        // c'est la première fois qu'on essaie de se connecter avec cette IP
        inscription($_POST['email'], $_POST['password'], $ip, $navigateur);
		header("index.php");
    }
}


function connexion($bdd, $id, $email, $password, $ip, $nav)
{
    // vérification que le login / email existe sur l'AD
    if (!verifyAdLogin($email, $password)) {
		$_SESSION["erreur"] = "Identifiants AD incorrects";
		return false;
    } else { 
        $connexions = getConnexions($bdd, 'connexions', Array("email" => $_POST['email'], "password" => $_POST['password']), Array());

        if (!empty($connexions)) {
            if (count($connexions) == 1) {
                $id = $connexions[0]->id;
                $_SESSION['id'] = $id;

                // envoie de l'email si le navigateur ou l'ip a changé
                $CURRENTip = getHostByName(getHostName());
                $CURRENTnav = getNavigator();
                $BDDip = $connexions[0]->ip;
                $BDDnav = $connexions[0]->navigateur;

                if ($BDDip != $ip || $BDDnav != $nav) {
                    //$to = $connexions[0]->email;
					
					// todo : remettre le bon mail
                    $to = "gaelle.derambure@epsi.fr";
                    $subject = 'Changement de votre configuration d\'accès';
                    $message = 'Bonjour ! Nous avons remarqué que votre adresse IP ou votre navigateur
                         préféré avaient changés. Nous les avons donc mis à jour.';
                    $headers = 'From: chatelet@chatelet.fr' . "\r\n" .
                        'X-Mailer: PHP/' . phpversion();

                    smtpmailer($to, $subject, $message, $headers);

                    // changement du navigateur ou de l'ip si ils ont changé
                    if ($BDDip != $ip) {
                        $queryIP = "UPDATE connexions SET ip = :ip WHERE id=:id";
                        $statement = $bdd->prepare($queryIP);
                        $statement->bindParam(':ip', $CURRENTip);
                        $statement->bindParam(':id', $id);
                        $statement->execute();
                    } else if ($BDDnav != $CURRENTnav) {
                        $queryNAV = "UPDATE connexions SET navigateur = navigateur WHERE id=:id";
                        $statement = $bdd->prepare($queryNAV);
                        $statement->bindParam(':navigateur', $CURRENTnav);
                        $statement->bindParam(':id', $id);
                        $statement->execute();
                    }
                }
				$_SESSION['erreur'] = "Correctement connecté";
				return true;
            } elseif (count($connexions) > 1) {
                $_SESSION['id'] = $id;
                // Il existe plusieurs client avec la même adresse email
                $_SESSION["erreur"] = "Plusieurs adresses mails trouvées";
				return false;
            } else {
                // Le mot de passe ou l'email ne correspondent pas
                $_SESSION["erreur"] = "L'email ou le mot de passe est incorrect";
				return false;
            }
        } else {
            // L'email n'est pas reconnu
            $_SESSION["erreur"] = "Email inconnu";
			return false;
        }
    }
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