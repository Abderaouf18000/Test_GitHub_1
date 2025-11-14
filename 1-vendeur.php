<?php
// Configuration de base
ini_set('memory_limit', '1024M');
session_start();

// Fonction pour formater les nombres avec séparateur de milliers
function formatNumber($number) {
    return number_format($number);
}

// Traitement des données CSV
$csvFile = '/Users/abderaoufbouhali/PycharmProjects/Mémoire/results/11-fusion_9_10_14-fin.csv';
$csvData = [];
$totalVendeurs = 0;
$totalVulnerabilites = 0;
$totalProduits = 0;

// Lecture et analyse du fichier CSV
if (file_exists($csvFile)) {
    $handle = fopen($csvFile, 'r');
    
    // Lire l'en-tête
    $header = fgetcsv($handle, 0, ",", "\"", "\\");
    
    // Lire les lignes de données
    while (($row = fgetcsv($handle, 0, ",", "\"", "\\")) !== false) {
        $csvData[] = array_combine($header, $row);
        $totalVendeurs++;
        
        // Calculer les totaux
        if (isset($row[1])) $totalVulnerabilites += intval($row[1]); // Vulnerabilities_Count
        if (isset($row[2])) $totalProduits += intval($row[2]); // Products_Count
    }
    
    fclose($handle);
}

// Structure des cartes de statistiques
$statsCards = [
    [
        'icon' => 'fas fa-building',
        'title' => 'Total Vendeurs',
        'value' => $totalVendeurs,
        'color' => 'text-blue'
    ],
    [
        'icon' => 'fas fa-bug',
        'title' => 'Total Vulnérabilités',
        'value' => $totalVulnerabilites,
        'color' => 'text-red'
    ],
    [
        'icon' => 'fas fa-cubes',
        'title' => 'Total Produits',
        'value' => $totalProduits,
        'color' => 'text-success'
    ]
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Dashboard des vendeurs avec analyse de vulnérabilités">
    <title>Dashboard des Vendeurs</title>

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Variables CSS pour une meilleure maintenance */
        :root {
            --bg-color: #f4f6f9;
            --card-bg: white;
            --text-primary: #212529;
            --text-muted: #6c757d;
            --blue-color: #0d6efd;
            --red-color: #dc3545;
            --green-color: #198754;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --border-radius: 8px;
            --transition-speed: 0.3s;
        }
        
        /* Styles de base */
        body {
            background-color: var(--bg-color);
            font-family: 'Arial', sans-serif;
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 15px;
        }
        
        /* En-tête de page */
        .page-header {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .page-header h1 {
            color: var(--text-primary);
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .page-header p {
            color: var(--text-muted);
            margin-bottom: 0;
        }
        
        /* Cartes statistiques */
        .stats-row {
            margin-bottom: 30px;
        }
        
        .stats-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform var(--transition-speed);
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-card h4 {
            color: #3a3a3a;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .stats-value {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 0;
        }
        
        .text-blue { color: var(--blue-color); }
        .text-red { color: var(--red-color); }
        .text-success { color: var(--green-color); }
        
        .stats-icon {
            font-size: 40px;
            margin-right: 15px;
        }
        
        /* Conteneur de tableau */
        .table-container {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-md);
        }
        
        .table-header {
            margin-bottom: 15px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 15px;
        }
        
        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0;
            color: var(--text-primary);
        }
        
        /* Styles de tableau */
        .table {
            border: 2px solid #000;
            margin-bottom: 0 !important;
        }
        
        .table th,
        .table td {
            border: 1px solid #000;
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fc;
        }
        
        .table-hover tbody tr:hover {
            background-color: #e2e6ea;
        }
        
        /* Boutons d'action */
        .btn-action {
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: 500;
            transition: all var(--transition-speed);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .btn-report {
            background-color: var(--blue-color);
            color: white;
            border: none;
        }
        
        .btn-report:hover {
            background-color: #0b5ed7;
        }
        
        /* Media queries pour la réactivité */
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 15px;
            }
            
            .page-header {
                padding: 15px;
                text-align: center;
            }
            
            .table-container {
                padding: 15px;
                overflow-x: auto;
            }
        }
        
        /* Styles pour la recherche avancée */
        .search-container {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-sm);
        }
        
        .search-container h3 {
            font-size: 1.1rem;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .search-group {
            margin-bottom: 10px;
        }
        
        .search-label {
            font-weight: 500;
            display: block;
            margin-bottom: 5px;
        }
        
        .search-input {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 6px 10px;
        }
        
        .search-input:focus {
            border-color: var(--blue-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-search {
            background-color: var(--blue-color);
            color: white;
            padding: 6px 15px;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-search:hover {
            background-color: #0b5ed7;
        }
        
        .btn-reset {
            background-color: #6c757d;
            color: white;
            padding: 6px 15px;
            font-weight: 500;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-reset:hover {
            background-color: #5a6268;
        }
        
        /* Styles pour le filtre rapide */
        .dt-search-container {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .dt-search-container label {
            margin-right: 10px;
            font-weight: 500;
        }
        
        .dt-search-input {
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            width: 200px;
        }
        
        /* Style pour l'indicateur de tri */
        .dataTables_wrapper .sorting:after,
        .dataTables_wrapper .sorting_asc:after,
        .dataTables_wrapper .sorting_desc:after {
            margin-left: 5px;
        }

        .session-box {
        background-color: #f8f9fa;
        border-radius: var(--border-radius);
        padding: 15px;
        box-shadow: var(--shadow-sm);
        height: 100%;
        border-left: 3px solid var(--blue-color);
    }
    
    .session-box.no-session {
        border-left-color: #ced4da;
    }
    
    .session-header {
        display: flex;
        align-items: center;
        font-weight: 600;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        color: var(--text-primary);
    }
    
    .session-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    
    .session-row {
        display: flex;
        font-size: 0.9rem;
    }
    
    .session-label {
        font-weight: 600;
        color: var(--text-muted);
        margin-right: 5px;
        width: 60px;
    }
    
    .session-value {
        color: var(--text-primary);
    }
    
    .session-empty-text {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.9rem;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .page-header .row {
            flex-direction: column;
        }
        
        .col-md-6:first-child {
            margin-bottom: 15px;
        }
        
        .session-box {
            margin-top: 10px;
        }
        
        .session-grid {
            grid-template-columns: 1fr;
        }
    }
    
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- En-tête de la page avec informations de session -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1>Liste des Vendeurs</h1>
            <p class="text-muted">Vue d'ensemble des vulnérabilités par vendeur</p>
        </div>
        <div class="col-md-6">
            <?php if (isset($_SESSION['current_log'])): ?>
                <div class="session-box">
                    <div class="session-header">
                        <i class="fas fa-check text-success me-2"></i>
                        <span>Analyse en cours</span>
                    </div>
                    <div class="session-grid">
                        <div class="session-row">
                            <div class="session-label">Type:</div>
                            <div class="session-value"><?= htmlspecialchars($_SESSION['current_log']['type']) ?></div>
                        </div>
                        <div class="session-row">
                            <div class="session-label">Année:</div>
                            <div class="session-value"><?= htmlspecialchars($_SESSION['current_log']['annee']) ?></div>
                        </div>
                        <div class="session-row">
                            <div class="session-label">Début:</div>
                            <div class="session-value"><?= htmlspecialchars($_SESSION['current_log']['start_time']) ?></div>
                        </div>
                        <div class="session-row">
                            <div class="session-label">Fichier:</div>
                            <div class="session-value text-truncate"><?= basename(htmlspecialchars($_SESSION['current_log']['file'])) ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="session-box no-session">
                    <div class="session-header">
                        <i class="fas fa-info-circle text-muted me-2"></i>
                        <span>Aucune analyse en cours</span>
                    </div>
                    <p class="session-empty-text">Utilisez le formulaire d'analyse pour lancer une nouvelle session.</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="return-button-container">
        <a href="0-Accuille.php" class="btn-return">
            <i class="fas fa-home me-2"></i>Retour à l'accueil
        </a>
    </div>
    </div>
</div>

    <!-- Cartes de statistiques -->
    <div class="row stats-row">
        <?php foreach ($statsCards as $card): ?>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="d-flex align-items-center">
                        <i class="<?= $card['icon'] ?> stats-icon <?= $card['color'] ?>"></i>
                        <div>
                            <h4><?= $card['title'] ?></h4>
                            <p class="stats-value <?= $card['color'] ?>"><?= formatNumber($card['value']) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Recherche avancée -->
    <div class="search-container">
        <h3><i class="fas fa-search me-2"></i>Recherche avancée</h3>
        <div class="row">
            <div class="col-md-4">
                <div class="search-group">
                    <label for="vendor-search" class="search-label">Nom du vendeur:</label>
                    <input type="text" id="vendor-search" class="form-control search-input" placeholder="Ex: Microsoft, Adobe...">
                </div>
            </div>
            <div class="col-md-4">
                <div class="search-group">
                    <label for="vuln-min" class="search-label">Vulnérabilités minimum:</label>
                    <input type="number" id="vuln-min" class="form-control search-input" min="0">
                </div>
            </div>
            <div class="col-md-4">
                <div class="search-group">
                    <label for="prod-min" class="search-label">Produits minimum:</label>
                    <input type="number" id="prod-min" class="form-control search-input" min="0">
                </div>
            </div>
        </div>
        <div class="mt-3">
            <button id="apply-search" class="btn-search me-2"><i class="fas fa-filter me-1"></i>Appliquer les filtres</button>
            <button id="reset-search" class="btn-reset"><i class="fas fa-undo me-1"></i>Réinitialiser</button>
        </div>
    </div>

    <!-- Tableau des vendeurs -->
    <div class="table-container">
        <div class="table-header">
            <h2 class="table-title">Détails des Vendeurs</h2>
        </div>
        <div class="table-responsive">
            <table id="vendors-table" class="table table-striped table-hover">
                <thead class="table-primary">
                <tr>
                    <th>Vendeur</th>
                    <th class="text-center">Vulnérabilités</th>
                    <th class="text-center">Produits</th>
                    <th class="text-center">Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($csvData)): ?>
                    <?php foreach ($csvData as $row): ?>
                        <?php
                        $vulnCount = intval($row['Vulnerabilities_Count']);
                        $prodCount = intval($row['Products_Count']);
                        ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars($row['Vendor']) ?></td>
                            <td class="text-center fw-semibold"><?= formatNumber($vulnCount) ?></td>
                            <td class="text-center"><?= formatNumber($prodCount) ?></td>
                            <td class="text-center">
                                <a href="3-statistique_vendeur.php?vendor=<?= urlencode($row['Vendor']) ?>"
                                   class="btn btn-report btn-action">
                                    <i class="fas fa-file-alt me-1"></i> Rapport
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Aucune donnée disponible</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Désactiver les messages d'erreur de DataTables
        $.fn.dataTable.ext.errMode = 'none';
        
        // Initialiser DataTables
        var table = $('#vendors-table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            },
            order: [[1, 'desc']],
            columnDefs: [
                { orderable: false, targets: 3 },
                { type: 'num-fmt', targets: [1, 2] } // Spécifier que les colonnes 1 et 2 contiennent des nombres formatés
            ],
            pageLength: 25,
            dom: 'lrtip', // Suppression de 'f' pour enlever le champ de recherche par défaut
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
            buttons: [] // Initialisation vide pour le dom
        });
        
        // Fonction pour appliquer les filtres
        $('#apply-search').on('click', function() {
            applyFilters();
        });
        
        // Fonction pour réinitialiser les filtres
        $('#reset-search').on('click', function() {
            $('#vendor-search').val('');
            $('#vuln-min').val('');
            $('#prod-min').val('');
            table.search('').columns().search('').draw();
        });
        
        // Appliquer les filtres lorsqu'on appuie sur Entrée dans un champ
        $('.search-input').on('keypress', function(e) {
            if (e.which === 13) {
                applyFilters();
            }
        });
        
        // Fonction pour appliquer tous les filtres
        function applyFilters() {
            var vendorSearch = $('#vendor-search').val();
            var vulnMin = $('#vuln-min').val();
            var prodMin = $('#prod-min').val();
            
            // Réinitialiser les filtres
            table.columns().search('').draw(false);
            
            // Appliquer la recherche par vendeur (colonne 0)
            if (vendorSearch) {
                table.column(0).search(vendorSearch, true, false);
            }
            
            // Filtrer les lignes selon les valeurs minimales de vulnérabilités et produits
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var vulnCount = parseFloat(data[1].replace(/[^\d.-]/g, '')) || 0;
                    var prodCount = parseFloat(data[2].replace(/[^\d.-]/g, '')) || 0;
                    
                    var vulnMinFilter = vulnMin ? parseFloat(vulnMin) : 0;
                    var prodMinFilter = prodMin ? parseFloat(prodMin) : 0;
                    
                    // Retourner true si toutes les conditions sont remplies
                    return (vulnCount >= vulnMinFilter && prodCount >= prodMinFilter);
                }
            );
            
            // Appliquer tous les filtres
            table.draw();
            
            // Nettoyer les filtres personnalisés
            $.fn.dataTable.ext.search.pop();
        }
        
        // Améliorer l'apparence des contrôles DataTables
        $('.dataTables_length select').addClass('form-select form-select-sm');
        $('.dataTables_filter input').addClass('form-control form-control-sm');
    });
</script>
</body>
</html>