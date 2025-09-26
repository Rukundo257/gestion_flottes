<?php
$host = "localhost";
$user = "root";      
$password = "";      
$dbname = "gestion_flottes_automobiles"; 

// Connexion avec PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    // Activer le mode exception pour les erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie à la base de données";
} catch (PDOException $e) {
    echo "Erreur de connexion : " . $e->getMessage();
}
?>
