<?php
require "PHPMailer/PHPMailerAutoload.php";

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
{
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

function inscription($email, $mdp, $ip, $nav)
{
    $bdd = getDataBase();

    $cle = rand(1000000, 9000000);

    $insererUser = $bdd->prepare('INSERT INTO connexions(email, password, ip, navigateur, nbTentatives, cle, confirme) VALUES(:email, :password, :ip, :navigateur, 0, :cle, 0)');
    $insererUser->bindParam(':email', $email);
    $insererUser->bindParam(':password', $mdp);
    $insererUser->bindParam(':ip', $ip);
    $insererUser->bindParam(':navigateur', $nav);
    $insererUser->bindParam(':cle', $cle);
    $insererUser->execute();

    $recupUser = $bdd->prepare('SELECT * FROM connexions WHERE email = :email');
    $recupUser->bindParam(':email', $email);
    $recupUser->execute();
    if (!empty($recupUser)) {
        $userInfos = $recupUser->fetch();
        $_SESSION['id'] = $userInfos['id'];

        // todo : remettre le bon mail
        $to = "gaelle.derambure@epsi.fr";
        $from = 'clinique@chatelet.local';
        $name = 'Le Chatelet';
        $subj = 'Email de confirmation de compte';
        $msg = '<a href = "http://mspradmin/verif.php?id=' . $_SESSION['id'] . '&cle=' . $cle .'">Verifier votre email'. '</a>';

        smtpmailer($to, $from, $name, $subj, $msg);
    }
}

function smtpmailer($to, $from, $from_name, $subject, $body)
{
    $mail = new PHPMailer();
    $mail->IsSMTP();
    $mail->SMTPAuth = true;

    $mail->SMTPSecure = 'ssl';
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = '465';
    $mail->Username = 'webmailerssl@gmail.com';
    $mail->Password = 'mailermailer';

    //   $path = 'reseller.pdf';
    //   $mail->AddAttachment($path);

    $mail->IsHTML(true);
    $mail->From = "webmailerssl@gmail.com";
    $mail->FromName = $from_name;
    $mail->Sender = $from;
    $mail->AddReplyTo($from, $from_name);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AddAddress($to);
    try {
        if (!$mail->Send()) {
            return "Please try Later, Error Occured while Processing...";
        } else {
            return "Email bien envoyé.";
        }
    } catch (phpmailerException $e) {
        echo $e;
    }
}

function getLocationInfoByIp($ip)
{
    $result = null;

    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
    if ($ip_data && $ip_data->geoplugin_countryName != null) {
        $result= $ip_data->geoplugin_countryCode;
    }
    return $result;
}

function getNavigator()
{
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== FALSE)
        return 'Internet explorer';
    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident') !== FALSE) //For Supporting IE 11
        return 'Internet explorer';
    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== FALSE)
        return 'Mozilla Firefox';
    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== FALSE)
        return 'Google Chrome';
    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== FALSE)
        return "Opera Mini";
    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== FALSE)
        return "Opera";
    elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== FALSE)
        return "Safari";
    else
        return 'Something else';
}

?>
