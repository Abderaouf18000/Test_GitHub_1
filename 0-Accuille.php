<?php
/**
 * Traitement des requêtes POST pour l'analyse CVE
 * Version avec suivi d'exécution pour macOS et mémoire des analyses par année
 */
session_start();

// Initialiser le tableau des analyses terminées s'il n'existe pas encore
if (!isset($_SESSION['completed_years'])) {
    $_SESSION['completed_years'] = null;
}

// Vérifier et nettoyer la session si nécessaire
if (isset($_SESSION['current_log']) && file_exists($_SESSION['current_log']['file'])) {
    // Vérifier si le fichier de log contient un marqueur de fin
    $logContent = file_get_contents($_SESSION['current_log']['file']);
    if (strpos($logContent, '[COMPLETED]') !== false || 
        strpos($logContent, 'Analyse terminée') !== false || 
        strpos($logContent, 'Processus terminé') !== false ||
        strpos($logContent, '100%') !== false) {
        // Modifiez cette partie dans votre code :
if (strpos($logContent, '[COMPLETED]') !== false || 
strpos($logContent, 'Analyse terminée') !== false || 
strpos($logContent, 'Processus terminé') !== false ||
strpos($logContent, '100%') !== false) {

// Enregistrer uniquement la dernière année analysée
$year = $_SESSION['current_log']['annee'];
$_SESSION['completed_years'] = [
    $year => [
        'derniere_analyse' => [
            'file' => $_SESSION['current_log']['file'],
            'type' => $_SESSION['current_log']['type'],
            'timestamp' => $_SESSION['current_log']['timestamp'],
            'completion_time' => date('Y-m-d H:i:s')
        ]
    ]
];
}
        
        // L'analyse est terminée, nettoyons la session
        //unset($_SESSION['current_log']);
    }
}

$message = null;
$message_type = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les paramètres
    $type = isset($_POST['type']) ? $_POST['type'] : '';
    $annee = isset($_POST['annee']) ? $_POST['annee'] : '';
    $forceRerun = isset($_POST['force_rerun']) ? (bool)$_POST['force_rerun'] : false;
    
    // Si le type est prediction_iot, rediriger vers la page de prédictions
    if ($type === 'prediction_iot') {
        header("Location: 11-prediction.php");
        exit;
    }
    
    // Valider les entrées
    if (empty($type) || empty($annee)) {
        $message = 'Les paramètres type et année sont requis';
        $message_type = 'danger';
    }
    // Vérifier que l'année est valide
    else if (!is_numeric($annee) || intval($annee) < 2010 || intval($annee) > 2025) {
        $message = 'L\'année sélectionnée est invalide';
        $message_type = 'danger';
    }
    // Vérifier que le type est valide
    else {
        $typesValides = ['vendor', 'product', 'category', 'advanced'];
        if (!in_array($type, $typesValides)) {
            $message = 'Le type d\'analyse est invalide';
            $message_type = 'danger';
        } else {
            // Vérifier si cette année a déjà été analysée
            if (!$forceRerun && isset($_SESSION['completed_years'][$annee])) {
                // Cette année a déjà été analysée
                $message = "Des résultats existent déjà pour l'année $annee. Le script ne sera pas réexécuté.";
                $message_type = 'info';
                
                // Rediriger vers la page de résultats en fonction du type d'analyse
                if (isset($_POST['redirect']) && $_POST['redirect'] == 'true') {
                    if ($type === 'product') {
                        header("Location: 7-liste_produit.php");
                        exit;
                    } else if ($type === 'category') {
                        header("Location: categorie/1-accueille_categorie.php");
                        exit;
                    } else if ($type === 'advanced') {
                        header("Location: 9-recherche_avanc.php");
                        exit;
                    } else {
                        // Par défaut, redirection vers la page vendeur
                        header("Location: 1-vendeur.php");
                        exit;
                    }
                }
            } else {
                // Chemin vers le script Python
                $pythonScript = '/Users/abderaoufbouhali/PycharmProjects/Mémoire/main.py';
                
                // Vérifier que le script Python existe
                if (!file_exists($pythonScript)) {
                    $message = 'Le script Python n\'existe pas';
                    $message_type = 'danger';
                } else {
                    // Créer un dossier pour les logs s'il n'existe pas
                    $logDir = "logs";
                    if (!is_dir($logDir)) {
                        mkdir($logDir, 0755, true);
                    }
                    
                    // Créer un fichier de log unique pour cette exécution
                    $timestamp = time();
                    $logFileName = "analyse_{$type}_{$annee}_{$timestamp}.log";
                    $logFile = $logDir . '/' . $logFileName;
                    
                    // Préparer la commande shell sécurisée
                    $typeParam = escapeshellarg($type);
                    $anneeParam = escapeshellarg($annee);
                    
                    // Commande pour Mac (redirection vers fichier de log)
                    $command = "/Users/abderaoufbouhali/.pyenv/versions/env_1/bin/python $pythonScript $anneeParam $typeParam > $logFile 2>&1 &";
                    
                    // Exécuter la commande
                    exec($command);
                    
                    // Sauvegarder les informations de l'exécution dans la session
                    $_SESSION['current_log'] = [
                        'file' => $logFile,
                        'type' => $type,
                        'annee' => $annee,
                        'timestamp' => $timestamp,
                        'start_time' => date('Y-m-d H:i:s')
                    ];
                    
                    // Message de succès avec lien vers la page de suivi
                    $message = "L'analyse a été lancée avec succès pour l'année $annee (type: $type). <a href='suivi.php' class='alert-link'>Cliquez ici pour suivre sa progression</a>.";
                    $message_type = 'success';
                    
                    // Rediriger vers la page de suivi si demandé
                    if (isset($_POST['redirect']) && $_POST['redirect'] == 'true') {
                        header("Location: 0-suivi.php");
                        exit;
                    }
                }
            }
        }
    }
}

// Récupérer l'année depuis les paramètres d'URL pour l'option "Forcer l'analyse"
$urlAnnee = isset($_GET['annee']) ? $_GET['annee'] : '';
$urlForceRerun = isset($_GET['force_rerun']) && $_GET['force_rerun'] === 'true';

if ($urlForceRerun && !empty($urlAnnee)) {
    $message = "Veuillez confirmer que vous souhaitez relancer l'analyse pour l'année $urlAnnee malgré les résultats existants.";
    $message_type = 'warning';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analyse des Archives CVE</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            width: 800px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .header {
            background-color: #1e3a8a;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 24px;
            margin: 0;
        }
        
        .content {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            color: #333;
        }
        
        .btn-primary {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border-radius: 6px;
            transition: background-color 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }
        
        .btn-primary:disabled {
            background-color: #9ca3af;
            border-color: #9ca3af;
        }
        
        .analysis-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .analysis-option {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .analysis-option:hover {
            background-color: #f8f9ff;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .analysis-option.selected {
            background-color: #eff6ff;
            border-color: #3b82f6;
            border-width: 2px;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2);
        }
        
        .option-title {
            font-weight: bold;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            color: #1e3a8a;
        }
        
        .option-title i {
            margin-right: 8px;
            color: #3b82f6;
        }
        
        .form-select {
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .step-number {
            display: inline-block;
            background-color: #1e3a8a;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            margin-right: 8px;
            font-size: 14px;
        }
        
        .year-badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e3a8a;
            padding: 5px 10px;
            border-radius: 20px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .years-processed {
            margin-top: 20px;
            background-color: #f8f9ff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 15px;
        }
        
        .years-processed h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #1e3a8a;
        }
        
        .prediction-option {
            background-color: #f0fdff;
            border-color: #22c55e;
            position: relative;
        }
        
        .prediction-option:hover {
            background-color: #e0f7fa;
            box-shadow: 0 2px 8px rgba(34, 197, 94, 0.2);
        }
        
        .prediction-option.selected {
            background-color: #e0f7fa;
            border-color: #22c55e;
        }
        
        .prediction-option .option-title i {
            color: #22c55e;
        }
        
        .new-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #f43f5e;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            transform: rotate(15deg);
        }
        
        .year-section {
            display: none;
        }
        
        .year-section.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-shield-alt"></i> Analyse des Archives CVE</h1>
        </div>
        
        <!-- Content -->
        <div class="content">
            <?php if ($message): ?>
            <!-- Affichage des messages -->
            <div class="alert alert-<?php echo $message_type; ?> mb-4">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : ($message_type === 'info' ? 'info-circle' : ($message_type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle')); ?>"></i> 
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <!-- Confirmation pour forcer l'analyse -->
            <?php if ($urlForceRerun && !empty($urlAnnee)): ?>
            <div class="alert alert-warning mb-4">
                <form method="post" action="">
                    <input type="hidden" name="type" value="vendor"> <!-- Type par défaut -->
                    <input type="hidden" name="annee" value="<?php echo htmlspecialchars($urlAnnee); ?>">
                    <input type="hidden" name="force_rerun" value="1">
                    <input type="hidden" name="redirect" value="true">
                    <p>Voulez-vous vraiment relancer l'analyse pour l'année <?php echo htmlspecialchars($urlAnnee); ?> malgré les résultats existants ?</p>
                    <button type="submit" class="btn btn-warning">Oui, relancer l'analyse</button>
                    <a href="0-Accuille.php" class="btn btn-secondary ms-2">Non, annuler</a>
                </form>
            </div>
            <?php else: ?>
            
            <!-- Form -->
            <form method="post" action="" id="analyseForm">
                <!-- Type d'analyse -->
                <div class="form-group">
                    <label class="form-label"><span class="step-number">1</span>Choisissez un type d'analyse:</label>
                    <div class="analysis-options">
                        <div class="analysis-option" data-type="vendor">
                            <div class="option-title">
                                <i class="fas fa-building"></i> Par Vendeur
                            </div>
                            <div>Analyse par entreprise ou fournisseur</div>
                        </div>
                        <div class="analysis-option" data-type="product">
                            <div class="option-title">
                                <i class="fas fa-laptop-code"></i> Par Produit
                            </div>
                            <div>Analyse par produit ou logiciel</div>
                        </div>
                        <div class="analysis-option" data-type="category">
                            <div class="option-title">
                                <i class="fas fa-layer-group"></i> Par Catégorie
                            </div>
                            <div>Analyse par type de vulnérabilité</div>
                        </div>
                        <div class="analysis-option" data-type="advanced">
                            <div class="option-title">
                                <i class="fas fa-search-plus"></i> Recherche Avancée
                            </div>
                            <div>Recherche avec critères multiples</div>
                        </div>
                        <div class="analysis-option prediction-option" data-type="prediction_iot">
                            <span class="new-badge">NEW</span>
                            <div class="option-title">
                                <i class="fas fa-chart-line"></i> Prédictions IoT
                            </div>
                            <div>Prédictions de vulnérabilités IoT par vendeur</div>
                        </div>
                    </div>
                    <input type="hidden" name="type" id="typeInput" value="">
                </div>
                
                <!-- Année d'archive - Section standard -->
                <div class="form-group year-section" id="yearSection">
                    <label class="form-label" for="yearSelect"><span class="step-number">2</span>Sélectionnez l'année à analyser:</label>
                    <select class="form-select" id="yearSelect" name="annee">
                        <option value="" selected disabled>Choisir une année</option>
                        <?php for ($i = 2025; $i >= 2015; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo isset($_SESSION['completed_years'][$i]) ? 'data-analyzed="true"' : ''; ?>>
                                <?php echo $i; ?>
                                <?php if (isset($_SESSION['completed_years'][$i])): ?>
                                    (Déjà analysée)
                                <?php endif; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <!-- Section pour les prédictions IoT (pas de sélection d'année) -->
                <div class="form-group year-section" id="predictionSection">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Les prédictions de vulnérabilités IoT affichent les vulnérabilités potentielles qui pourraient être découvertes dans les appareils IoT des principaux vendeurs.
                    </div>
                </div>
                
                <!-- Bouton d'analyse -->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" id="analyzeBtn" disabled>
                        <i class="fas fa-search"></i> <span id="buttonText">Lancer l'analyse</span>
                    </button>
                </div>
                
                <!-- Option pour rediriger (facultatif) -->
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="redirect" value="true" id="redirectCheck" checked>
                    <label class="form-check-label" for="redirectCheck">
                        Ouvrir automatiquement la page de suivi ou de résultats
                    </label>
                </div>
            </form>
            <?php endif; ?>
            
            <!-- Années déjà analysées -->
            <?php if (!empty($_SESSION['completed_years'])): ?>
            <div class="years-processed mt-4">
                <h3><i class="fas fa-history"></i> Années déjà analysées</h3>
                <div>
                    <?php foreach (array_keys($_SESSION['completed_years']) as $year): ?>
                        <span class="year-badge">
                            <?php echo $year; ?>
                            <a href="?force_rerun=true&annee=<?php echo $year; ?>" title="Forcer une nouvelle analyse" class="ms-2">
                                <i class="fas fa-redo-alt"></i>
                            </a>
                        </span>
                    <?php endforeach; ?>
                </div>
                <div class="mt-3 small text-muted">
                    Note: Pour ces années, l'analyse ne sera pas relancée à moins que vous ne forciez une nouvelle analyse.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Gestion de la sélection des options d'analyse
const analysisOptions = document.querySelectorAll('.analysis-option');
let selectedAnalysisType = null;

analysisOptions.forEach(option => {
    option.addEventListener('click', function() {
        // Retirer la sélection de toutes les options
        analysisOptions.forEach(opt => opt.classList.remove('selected'));
        
        // Sélectionner cette option
        this.classList.add('selected');
        selectedAnalysisType = this.getAttribute('data-type');
        
        // Mettre à jour le champ caché
        document.getElementById('typeInput').value = selectedAnalysisType;
        
        // Afficher la section appropriée
        const yearSection = document.getElementById('yearSection');
        const predictionSection = document.getElementById('predictionSection');
        
        if (selectedAnalysisType === 'prediction_iot') {
            yearSection.classList.remove('active');
            predictionSection.classList.add('active');
            document.getElementById('buttonText').textContent = 'Voir les prédictions';
            document.getElementById('analyzeBtn').disabled = false;
        } else {
            yearSection.classList.add('active');
            predictionSection.classList.remove('active');
            document.getElementById('buttonText').textContent = 'Lancer l\'analyse';
            // Mettre à jour l'état du bouton
            updateAnalyzeButtonState();
        }
    });
});

// Gestion du changement dans le sélecteur d'année
document.getElementById('yearSelect').addEventListener('change', function() {
    updateAnalyzeButtonState();
    
    // Vérifier si l'année a déjà été analysée et afficher un message si nécessaire
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.getAttribute('data-analyzed') === 'true') {
        alert("Cette année a déjà été analysée. L'analyse ne sera pas relancée, vous serez redirigé vers les résultats existants.");
    }
});

// Fonction pour mettre à jour l'état du bouton d'analyse
function updateAnalyzeButtonState() {
    const yearSelect = document.getElementById('yearSelect');
    const analyzeBtn = document.getElementById('analyzeBtn');
    
    if (selectedAnalysisType === 'prediction_iot') {
        analyzeBtn.disabled = false;
        return;
    }
    
    // Activer le bouton si un type d'analyse est sélectionné et une année est choisie
    if (selectedAnalysisType && yearSelect.value !== '') {
        analyzeBtn.disabled = false;
    } else {
        analyzeBtn.disabled = true;
    }
}

// Initialiser l'interface
document.addEventListener('DOMContentLoaded', function() {
    // Afficher la section d'année par défaut
    document.getElementById('yearSection').classList.add('active');
    
    // Pré-sélectionner l'année depuis les paramètres d'URL
    <?php if (!empty($urlAnnee)): ?>
    // Sélectionner le type par défaut (premier)
    const typeOption = document.querySelector('.analysis-option');
    if (typeOption) {
        typeOption.click();
    }
    
    // Sélectionner l'année
    const yearSelect = document.getElementById('yearSelect');
    yearSelect.value = "<?php echo htmlspecialchars($urlAnnee); ?>";
    
    // Mettre à jour l'état du bouton
    updateAnalyzeButtonState();
    <?php endif; ?>
});
    </script>
</body>
</html>