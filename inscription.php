<?php
require "PHPMailer/PHPMailerAutoload.php";
require('functions.php');

//session_start();
$bdd = getDataBase();

if (isset($_POST['valider'])) {
    if (!empty($_POST['email'])) {
        $cle = rand(1000000, 9000000);
        $email = $_POST['email'];

        //todo : implémenter nav et ip
        $insererUser = $bdd->prepare('INSERT INTO connexions(email, password, ip, navigateur, nbTentatives, cle, confirme)VALUES(:email, "", "", "", 0, :cle, 0)');
        $insererUser->bindParam(':email', $email);
        $insererUser->bindParam(':cle', $cle);
        $insererUser->execute();

        $recupUser = $bdd->prepare('SELECT * FROM connexions WHERE email = ?');
        $recupUser->execute(array($email));
        if ($recupUser->rowCount() > 0) {
            $userInfos = $recupUser->fetch();
            $_SESSION['id'] = $userInfos['id'];

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
                }

            }

            $to = $email;
            $from = 'webmailerssl@gmail.com';
            $name = 'WebMailer';
            $subj = 'Email de confirmation de compte';
            $msg = 'http://mspradmin/verif.php?id=' . $_SESSION['id'] . '&cle=' . $cle;

            $error = smtpmailer($to, $from, $name, $subj, $msg);
        }
    } else {
        echo "Veuilliez mettre votre email";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<body>
<form method="POST" action="">

    <h1>Inscription</h1>

    <input type="email" name="email">
    <br>
    <input type="submit" name="valider">

</form>
</body>
</html>
