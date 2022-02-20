<?php

/////////// exemple ppe 

require_once("head.php");

$bdd = getDatabase();

var_dump($_POST);

if (isset($bdd)) {
    $_SESSION["erreur"] = null;
    if (isset($_POST['password']) AND isset($_POST['email'])) {
        $password = htmlspecialchars($_POST['password']);
        $liste = getConnexions($bdd, 'connexions', Array("email" => $_POST['email'], "password" => $_POST['password']), Array());
        var_dump($liste);
        if (!empty($liste)) {
            if (count($liste) == 1) {
                $id = $liste[0]->id;
                $_SESSION['id'] = $id;
            } elseif (count($liste) > 1) {
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
} else {
    $_SESSION["erreur"] = 7;

}


if ($_SESSION["erreur"] != null) {
    header('Location: index.php');
} else {
    header('Location: index.php');
}





//////////// exemple site

/*
// Si on tente de s'identifier
if(!empty($_POST['email']) AND !empty($_POST['password']))
{
    // On initialise $existence_ft
    $existence_ft = '';

    // Si le fichier existe, on le lit
    if(file_exists('antibrute/'.$_POST['email'].'.tmp'))
    {
        // On ouvre le fichier
        $fichier_tentatives = fopen('antibrute/'.$_POST['email'].'.tmp', 'r+');

        // On récupère son contenu dans la variable $infos_tentatives
        $contenu_tentatives = fgets($fichier_tentatives);

        // On découpe le contenu du fichier pour récupérer les informations
        $infos_tentatives = explode(';', $contenu_tentatives);


        // Si la date du fichier est celle d'aujourd'hui, on récupère le nombre de tentatives
        if($infos_tentatives[0] == date('d/m/Y'))
        {
            $tentatives = $infos_tentatives[1];
        }
        // Si la date du fichier est dépassée, on met le nombre de tentatives à 0 et $existence_ft à 2
        else
        {
            $existence_ft = 2;
            $tentatives = 0; // On met la variable $tentatives à 0
        }
    }

    // Si le fichier n'existe pas encore, on met la variable $existence_ft à 1 et on met les $tentatives à 0
    else
    {
        $existence_ft = 1;
        $tentatives = 0;
    }


    // S'il y a eu moins de 30 identifications ratées dans la journée, on laisse passer
    if($tentatives < 30)
    {
        $verifications = mysql_query('SELECT email, password FROM connexions WHERE email = \''.mysql_real_escape_string($_POST['email']).'\' ');

        $data_verif = mysql_fetch_assoc($verifications);

        // Si le email existe bien
        if(!empty($data_verif['email']))
        {

            // Si le mot de passe est bon
            if($data_verif['password'] == trim($_POST['password']))
            {
                //------------------------------------------------
                // Ici Votre script qui identifie le membre
                //------------------------------------------------
            }
            // Si le mot de passe est faux
            else
            {

                // Si le fichier n'existe pas encore, on le créé
                if($existence_ft == 1)
                {
                    $creation_fichier = fopen('antibrute/'.$data_verif['email'].'.tmp', 'a+'); // On créé le fichier puis on l'ouvre
                    fputs($creation_fichier, date('d/m/Y').';1'); // On écrit à l'intérieur la date du jour et on met le nombre de tentatives à 1
                    fclose($creation_fichier); // On referme
                }
                // Si la date n'est plus a jour
                elseif($existence_ft == 2)
                {
                    fseek($fichier_tentatives, 0); // On remet le curseur au début du fichier
                    fputs($fichier_tentatives, date('d/m/Y').';1 '); // On met à jour le contenu du fichier (date du jour;1 tentatives)
                }
                else
                {

                    // Si la variable $tentatives est sur le point de passer à 30, on en informe l'administrateur du site
                    if($tentatives == 29)
                    {
                        $email_administrateur = 'Email de administrateur du site';

                        $sujet_notification = '[Site] Un compte membre a atteint son quota';

                        $message_notification = 'Un des comptes a atteint le quota de mauvais mots de passe journalier :';
                        $message_notification .= $data_verif['email'].' - '.$_SERVER['REMOTE_ADDR'].' - '.gethostbyaddr($_SERVER['REMOTE_ADDR']);

                        email($email_administrateur, $sujet_notification, $message_notification);
                    }

                    fseek($fichier_tentatives, 11); // On place le curseur juste devant le nombre de tentatives
                    fputs($fichier_tentatives, $tentatives + 1); // On ajoute 1 au nombre de tentatives
                }
                echo 'Mot de passe incorrect';
            }

        }
        // Si le email n'existe pas
        else
        {
            echo 'Email incorrect';
        }


    }
    // S'il y a déjà eu 30 tentatives dans la journée, on affiche un message d'erreur
    else
    {
        echo 'Trop de tentatives d\'authentification aujourd\'hui';
    }

    // Si on a ouvert un fichier, on le referme (eh oui, il ne faut pas l'oublier)
    if($existence_ft != 1)
    {
        fclose($fichier_tentatives);
    }
}
*/
?>