<?php
session_start();

// Vérifier si une analyse est en cours
if (!isset($_SESSION['current_log'])) {
    header('Location: 0-Accuille.php');
    exit;
}

// Récupérer le processus Python en cours
$cmd = "ps aux | grep python | grep '" . $_SESSION['current_log']['annee'] . ".*" . $_SESSION['current_log']['type'] . "' | grep -v grep";
exec($cmd, $output);

// Trouver le PID à partir de la sortie
$pid = null;
foreach ($output as $line) {
    $parts = preg_split('/\s+/', trim($line));
    if (count($parts) > 1) {
        $pid = $parts[1]; // Le PID est généralement le deuxième élément
        break;
    }
}

// Si un PID a été trouvé, tuer le processus
if ($pid) {
    exec("kill -9 $pid");
    $message = "Le processus (PID: $pid) a été arrêté.";
} else {
    $message = "Aucun processus correspondant n'a été trouvé.";
}

// Nettoyer la session
unset($_SESSION['current_log']);

// Rediriger avec un message
$_SESSION['message'] = $message;
$_SESSION['message_type'] = 'info';
header('Location: 0-Accuille.php');
exit;