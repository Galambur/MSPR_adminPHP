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
            <!-- L'input pour le nom du client -->
            <label for="email">Email :</label>
            <input type="text" name="email" value=""/><br/><br/>

            <!-- L'input pour le prénom du client -->
            <label for="password">Mot de passe :</label>
            <input type="text" name="password" value=""/><br/><br/>

            <!-- Bouton valider, au clic de ce bouton, le compte sera créé -->
            <input type="submit" class="button_add" value="Valider"/><br>
        </form>

        <?php
            echo $_SESSION["id"];
        ?>
    </div>
</body>
</html>
