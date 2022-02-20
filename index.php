<html>
<body>
<div class="index_body">
    <header>
        <?php
            include("head.php");
        ?>
    </header>
    <div class="centered_body">
        <!-- Titre de la page -->
        <h2>Se connecter</h2>
        <form action="login.php" method="post">
            <label for="email">Email :</label>
            <input type="text" name="email" value=""/><br/><br/>

            <label for="password">Mot de passe :</label>
            <input type="text" name="password" value=""/><br/><br/>

            <label for="ip">ip :</label>
            <input type="text" name="ip" value=""/><br/><br/>

            <input type="submit" class="button_add" value="Valider"/><br>
        </form>
    </div>
</body>
</html>
