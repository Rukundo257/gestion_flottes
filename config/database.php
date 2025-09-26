    <?php
    $host = "localhost";
    $user = "root";
    $password = ""; // Vérifie si ton XAMPP a un mot de passe défini
    $dbname = "gestion_flottes_automobiles";

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage() . " (Vérifiez $host, $user, $password, $dbname)");
    }

    return $pdo;
    ?>