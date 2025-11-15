<?php
// Si vous voulez renvoyer une page HTML au lieu de JSON, changez l'en-tête
header('Content-Type: text/html');

// Vérifier si les paramètres sont présents
if (!isset($_POST['annee']) || !isset($_POST['type'])) {
    echo '<script>alert("Année ou type manquant"); window.history.back();</script>';
    exit;
}

$annee = $_POST['annee'];
$type = $_POST['type'];

// Validation des entrées
if (!ctype_digit($annee)) {
    echo '<script>alert("Année invalide"); window.history.back();</script>';
    exit;
}

// Le reste de votre code d'exécution...

// À la fin, afficher un message et rediriger
if ($return_var !== 0) {
    echo '<script>
        alert("Erreur lors de l\'exécution du script Python");
        window.history.back();
    </script>';
} else {
    echo '<script>
        alert("Paramètres reçus - Année: ' . htmlspecialchars($annee) . ', Type: ' . htmlspecialchars($type) . '");
        window.location.href = "1-vendeur.php?annee=' . urlencode($annee) . '&type=' . urlencode($type) . '";
    </script>';
}
?>