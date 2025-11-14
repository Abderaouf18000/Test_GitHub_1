<?php
/**
 * suivi.php - Version compacte et centrée avec animation de progression
 */
session_start();

// Vérifier si une analyse est en cours
if (!isset($_SESSION['current_log']) || !file_exists($_SESSION['current_log']['file'])) {
    header('Location: 1-vendeur.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi d'exécution - <?php echo $_SESSION['current_log']['type']; ?> (<?php echo $_SESSION['current_log']['annee']; ?>)</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            width: 90%;
            max-width: 700px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            overflow: hidden;
        }
        
        .header {
            background-color: #336699;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }
        
        .content {
            padding: 15px;
        }
        
        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .status {
            display: flex;
            align-items: center;
            font-weight: bold;
            color: #336699;
        }
        
        .status.completed {
            color: #22aa33;
        }
        
        .status i {
            margin-right: 5px;
        }
        
        .progress-container {
            margin-bottom: 15px;
        }
        
        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .progress-bar-container {
            height: 8px;
            background-color: #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #336699;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 13px;
        }
        
        .info-item {
            flex: 1;
        }
        
        .info-label {
            color: #777;
            margin-bottom: 3px;
        }
        
        .log-container {
            height: 250px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 10px;
            background-color: #f8f8f8;
            border-bottom: 1px solid #ddd;
        }
        
        .log-content {
            flex: 1;
            padding: 10px;
            font-family: monospace;
            font-size: 12px;
            line-height: 1.4;
            overflow-y: auto;
            background-color: #fafafa;
        }
        
        .log-line {
            margin: 0;
            white-space: pre-wrap;
        }
        
        .log-success {
            color: #22aa33;
            font-weight: bold;
        }
        
        .log-error {
            color: #cc3333;
            font-weight: bold;
        }
        
        .actions {
            display: flex;
            justify-content: space-between;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            background-color: #f8f8f8;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #eee;
        }
        
        .btn-danger {
            background-color: #cc3333;
            color: white;
            border-color: #bb2222;
        }
        
        .btn-danger:hover {
            background-color: #bb2222;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        .completion-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        
        .completion-overlay.visible {
            opacity: 1;
            pointer-events: all;
        }
        
        .completion-box {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            padding: 20px;
            text-align: center;
            width: 250px;
        }
        
        .checkmark {
            color: #22aa33;
            font-size: 48px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Animation de fin -->
    <div class="completion-overlay" id="completionOverlay">
        <div class="completion-box">
            <div class="checkmark">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Analyse terminée</h3>
            <p>Redirection dans <span id="redirectTimer">3</span> secondes...</p>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <i class="fas fa-chart-line me-2"></i> Suivi d'analyse <?php echo $_SESSION['current_log']['type']; ?> - <?php echo $_SESSION['current_log']['annee']; ?>
        </div>
        
        <div class="content">
            <!-- Statut et temps écoulé -->
            <div class="status-row">
                <div class="status" id="statusBadge">
                    <i class="fas fa-spinner fa-spin"></i> En cours
                </div>
                <div>
                    <span id="duration">Calcul en cours...</span>
                </div>
            </div>
            
            <!-- Progression -->
            <div class="progress-container">
                <div class="progress-header">
                    <div id="currentStep">Initialisation</div>
                    <div id="progressPercent">0%</div>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                </div>
            </div>
            
            <!-- Informations -->
            <div class="info-row">
                <div class="info-item">
                    <div class="info-label">Démarré le</div>
                    <div><?php echo $_SESSION['current_log']['start_time']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Dernière mise à jour</div>
                    <div id="lastUpdate">-</div>
                </div>
            </div>
            
            <!-- Journal d'exécution -->
            <div class="log-container">
                <div class="log-header">
                    <div>Journal d'exécution</div>
                    <button class="btn btn-sm py-0 px-2" id="refreshBtn">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <div class="log-content" id="logContent">
                    Chargement du journal...
                </div>
            </div>
            
            <!-- Actions -->
            <div class="actions">
                <a href="1-vendeur.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <a href="0-stop_execution.php" class="btn btn-danger" id="stopBtn" onclick="return confirm('Êtes-vous sûr de vouloir arrêter l\'exécution?')">
                    <i class="fas fa-stop"></i> Arrêter l'exécution
                </a>
            </div>
        </div>
    </div>

    <script>
        // Variables globales
        const logFile = "<?php echo $_SESSION['current_log']['file']; ?>";
        const startTime = <?php echo $_SESSION['current_log']['timestamp']; ?>;
        let refreshInterval;
        let isCompleted = false;
        let lastContent = "";
        
        // Variables pour l'animation de la barre de progression
        let currentProgress = 0;
        let targetProgress = 0;
        let animationFrameId = null;
        let progressInterval;
        
        // Étapes de progression avec pourcentages associés
        const progressSteps = [
            {name: 'Initialisation', percent: 0},
            {name: 'Chargement des données', percent: 15},
            {name: 'Analyse en cours', percent: 30},
            {name: 'Traitement des résultats', percent: 60},
            {name: 'Finalisation', percent: 85},
            {name: 'Terminé', percent: 100}
        ];
        
        // Fonction pour formater le temps écoulé
        function formatDuration(seconds) {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = Math.floor(seconds % 60);
            
            let result = '';
            if (hours > 0) result += hours + 'h ';
            if (minutes > 0 || hours > 0) result += minutes + 'm ';
            result += secs + 's';
            
            return result;
        }
        
        // Fonction pour mettre à jour la durée
        function updateDuration() {
            const now = Math.floor(Date.now() / 1000);
            const duration = now - startTime;
            document.getElementById('duration').textContent = formatDuration(duration);
            document.getElementById('lastUpdate').textContent = new Date().toLocaleTimeString();
        }
        
        // Fonction pour animer la barre de progression de façon fluide
        function animateProgressBar() {
            if (Math.abs(currentProgress - targetProgress) < 0.5) {
                currentProgress = targetProgress;
            } else {
                currentProgress += (targetProgress - currentProgress) * 0.1;
            }
            
            const progressBar = document.getElementById('progressBar');
            const progressPercent = document.getElementById('progressPercent');
            
            progressBar.style.width = `${currentProgress}%`;
            progressPercent.textContent = `${Math.round(currentProgress)}%`;
            
            // Mettre à jour l'étape actuelle en fonction du pourcentage
            for (let i = progressSteps.length - 1; i >= 0; i--) {
                if (currentProgress >= progressSteps[i].percent) {
                    document.getElementById('currentStep').textContent = progressSteps[i].name;
                    break;
                }
            }
            
            // Continuer l'animation si nous n'avons pas atteint la cible
            if (currentProgress !== targetProgress) {
                animationFrameId = requestAnimationFrame(animateProgressBar);
            } else {
                animationFrameId = null;
            }
            
            // Si la progression est terminée
            if (currentProgress >= 100 && targetProgress >= 100) {
                markAsCompleted();
            }
        }
        
        // Fonction pour mettre à jour la progression cible
        function updateProgressBar(percent) {
            targetProgress = percent;
            
            // Démarrer l'animation si elle n'est pas déjà en cours
            if (animationFrameId === null) {
                animationFrameId = requestAnimationFrame(animateProgressBar);
            }
        }
        
        // Fonction pour incrémenter automatiquement la progression
        function startAutomaticProgress() {
            // Commencer à 5%
            updateProgressBar(5);
            
            // Incrémenter de 5% chaque seconde jusqu'à 95%
            progressInterval = setInterval(() => {
                // N'augmenter que si on n'est pas déjà terminé
                if (!isCompleted && targetProgress < 95) {
                    updateProgressBar(targetProgress + 5);
                }
                // Si on atteint ou dépasse 95%, ne plus incrémenter automatiquement
                if (targetProgress >= 95) {
                    clearInterval(progressInterval);
                }
            }, 1000); // Toutes les secondes
        }
        
        // Fonction pour formater le contenu du log
        function formatLogContent(content) {
            const lines = content.split('\n');
            let formattedContent = '';
            
            // Afficher les 100 dernières lignes maximum
            const startLine = Math.max(0, lines.length - 100);
            
            for (let i = startLine; i < lines.length; i++) {
                const line = lines[i];
                if (line.includes('✅') || line.includes('Réussi') || line.includes('Succès')) {
                    formattedContent += `<div class="log-line log-success">${line}</div>`;
                } else if (line.includes('❌') || line.includes('Échec') || line.includes('Erreur')) {
                    formattedContent += `<div class="log-line log-error">${line}</div>`;
                } else {
                    formattedContent += `<div class="log-line">${line}</div>`;
                }
            }
            
            return formattedContent;
        }
        
        // Fonction pour afficher l'animation de fin
        function showCompletionAnimation() {
            const overlay = document.getElementById('completionOverlay');
            overlay.classList.add('visible');
            
            // Compte à rebours pour la redirection
            let timer = 3;
            const timerElement = document.getElementById('redirectTimer');
            
            const countdown = setInterval(() => {
                timer--;
                timerElement.textContent = timer;
                
                if (timer <= 0) {
                    clearInterval(countdown);
                    window.location.href = '1-vendeur.php';
                }
            }, 1000);
        }
        
        // Fonction pour marquer l'exécution comme terminée
        function markAsCompleted() {
            if (!isCompleted) {
                isCompleted = true;
                
                // Mettre à jour le statut
                const statusBadge = document.getElementById('statusBadge');
                statusBadge.classList.add('completed');
                statusBadge.innerHTML = '<i class="fas fa-check-circle"></i> Terminé';
                
                // Arrêter les intervalles
                clearInterval(refreshInterval);
                clearInterval(progressInterval);
                
                // Désactiver le bouton d'arrêt
                const stopBtn = document.getElementById('stopBtn');
                stopBtn.classList.add('disabled');
                stopBtn.style.pointerEvents = 'none';
                stopBtn.style.opacity = '0.6';
                
                // Nettoyer la session
                cleanupSession();
                
                // Afficher l'animation de fin après un court délai
                setTimeout(() => {
                    showCompletionAnimation();
                }, 700);
            }
        }
        
        // Fonction pour nettoyer la session quand le traitement est terminé
        function cleanupSession() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'cleanup_session.php', true);
            xhr.send();
        }
        
        // Fonction pour rafraîchir le contenu du log
        function refreshLog() {
            fetch('0-get_log.php?file=' + encodeURIComponent(logFile) + '&t=' + new Date().getTime())
                .then(response => response.text())
                .then(data => {
                    if (data !== lastContent) {
                        const logContent = document.getElementById('logContent');
                        logContent.innerHTML = formatLogContent(data);
                        
                        // Défiler vers le bas pour voir les dernières lignes
                        logContent.scrollTop = logContent.scrollHeight;
                        
                        lastContent = data;
                    }
                    
                    // Mettre à jour la durée
                    updateDuration();
                    
                    // Vérifier si le traitement est terminé
                    if (data.includes('[COMPLETED]') || 
                        data.includes('Analyse terminée') || 
                        data.includes('Processus terminé') ||
                        data.includes('100%')) {
                        
                        if (!isCompleted) {
                            updateProgressBar(100);
                        }
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                });
        }
        
        // Initialisation au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Configurer le bouton de rafraîchissement manuel
            document.getElementById('refreshBtn').addEventListener('click', refreshLog);
            
            // Première exécution immédiate
            refreshLog();
            
            // Démarrer la progression automatique
            startAutomaticProgress();
            
            // Rafraîchissement périodique des logs
            refreshInterval = setInterval(refreshLog, 2000);
        });
    </script>
</body>
</html>