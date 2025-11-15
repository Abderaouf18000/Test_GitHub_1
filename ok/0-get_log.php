<?php
/**
 * get_log.php - Script pour récupérer le contenu d'un fichier de log
 */
session_start();

// Vérification de sécurité
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('HTTP/1.1 400 Bad Request');
    echo "Erreur: Aucun fichier spécifié";
    exit;
}

$file = $_GET['file'];

// Vérifier si le fichier demandé correspond à celui de la session en cours
if (!isset($_SESSION['current_log']) || $_SESSION['current_log']['file'] !== $file) {
    header('HTTP/1.1 403 Forbidden');
    echo "Erreur: Accès non autorisé à ce fichier";
    exit;
}

// Vérifier que le fichier existe
if (!file_exists($file)) {
    header('HTTP/1.1 404 Not Found');
    echo "Erreur: Fichier non trouvé";
    exit;
}

// Renvoyer le contenu du fichier
$content = file_get_contents($file);
echo $content;