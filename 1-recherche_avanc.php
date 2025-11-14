<?php
/**
 * recherche-avancee.php - Version compacte et structurée
 */
session_start();

// Chemin vers le fichier CSV
$csv_file = '7-catégorie_cwe_name.csv';

// Fonction pour obtenir les valeurs uniques d'une colonne dans le CSV
function getUniqueValuesFromCSV($file, $column) {
    $values = [];
    if (file_exists($file) && ($handle = fopen($file, "r")) !== FALSE) {
        // Lire l'en-tête pour obtenir les indices de colonnes
        $header = fgetcsv($handle, 0, ",", "\"", "\\");
        $columnIndex = array_search($column, $header);
        
        if ($columnIndex !== false) {
            // Lire les données ligne par ligne
            while (($data = fgetcsv($handle, 0, ",", "\"", "\\")) !== FALSE) {
                if (isset($data[$columnIndex]) && !empty($data[$columnIndex])) {
                    $values[] = $data[$columnIndex];
                }
            }
        }
        fclose($handle);
    }
    
    // Supprimer les doublons et trier
    $values = array_unique($values);
    sort($values);
    
    return $values;
}

// Obtenir les valeurs uniques pour les menus déroulants
$vendors = getUniqueValuesFromCSV($csv_file, 'Vendor');
$categories = getUniqueValuesFromCSV($csv_file, 'Category');
$families = getUniqueValuesFromCSV($csv_file, 'Family');
$cwe_categories = getUniqueValuesFromCSV($csv_file, 'Categorie_CWE');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche Avancée de Vulnérabilités</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- DatePicker CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }
        
        .search-container {
            max-width: 1000px;
            margin: 20px auto;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .search-header {
            padding: 12px 15px;
            background-color: #3b82f6;
            color: white;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        
        .search-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        
        .search-content {
            padding: 15px;
        }
        
        .form-section {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #374151;
            position: relative;
            padding-left: 12px;
        }
        
        .section-title::before {
            content: "";
            position: absolute;
            left: 0;
            top: 2px;
            bottom: 2px;
            width: 4px;
            background-color: #3b82f6;
            border-radius: 2px;
        }
        
        .form-control {
            height: auto;
            padding: 6px 10px;
            font-size: 14px;
            border-color: #e5e7eb;
        }
        
        .form-label {
            font-weight: 500;
            font-size: 13px;
            margin-bottom: 4px;
            color: #4b5563;
        }
        
        .btn-submit {
            background-color: #3b82f6;
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
        }
        
        .btn-submit:hover {
            background-color: #2563eb;
        }
        
        .btn-reset {
            background-color: #9ca3af;
            color: white;
            border: none;
            padding: 8px 15px;
            font-size: 14px;
            font-weight: 500;
            border-radius: 4px;
        }
        
        .btn-reset:hover {
            background-color: #6b7280;
        }
        
        .mb-2 {
            margin-bottom: 8px !important;
        }
        
        .date-field {
            position: relative;
        }
        
        .date-field .form-control {
            padding-right: 30px;
        }
        
        .date-field i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            pointer-events: none;
        }
        
        .score-range {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .score-range span {
            color: #6b7280;
            font-size: 13px;
        }
        
        .severity-selector {
            display: flex;
            gap: 5px;
        }
        
        .severity-item {
            flex: 1;
            padding: 6px 8px;
            text-align: center;
            font-size: 13px;
            border-radius: 3px;
            cursor: pointer;
            border: 1px solid;
            transition: all 0.15s;
        }
        
        .severity-item.active {
            color: white !important;
        }
        
        .severity-critical {
            color: #dc2626;
            border-color: #dc2626;
        }
        
        .severity-critical.active {
            background-color: #dc2626;
        }
        
        .severity-high {
            color: #ea580c;
            border-color: #ea580c;
        }
        
        .severity-high.active {
            background-color: #ea580c;
        }
        
        .severity-medium {
            color: #ca8a04;
            border-color: #ca8a04;
        }
        
        .severity-medium.active {
            background-color: #ca8a04;
        }
        
        .severity-low {
            color: #65a30d;
            border-color: #65a30d;
        }
        
        .severity-low.active {
            background-color: #65a30d;
        }
        
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .category-item {
            position: relative;
            padding-left: 24px;
            margin-bottom: 4px;
            font-size: 13px;
            cursor: pointer;
            user-select: none;
        }
        
        .category-item input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        
        .checkmark {
            position: absolute;
            top: 1px;
            left: 0;
            height: 16px;
            width: 16px;
            border: 1px solid #d1d5db;
            border-radius: 3px;
            background-color: #f9fafb;
        }
        
        .category-item:hover input ~ .checkmark {
            background-color: #f3f4f6;
        }
        
        .category-item input:checked ~ .checkmark {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        
        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
        }
        
        .category-item input:checked ~ .checkmark:after {
            display: block;
        }
        
        .category-item .checkmark:after {
            left: 5px;
            top: 2px;
            width: 5px;
            height: 9px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        
        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .categories-grid {
                grid-template-columns: repeat(1, 1fr);
            }
        }
        
        /* Style pour sélecteurs de famille et catégorie */
        .select2-container {
            width: 100% !important;
        }
        
        .select2-container--default .select2-selection--multiple {
            border-color: #e5e7eb;
            min-height: 38px;
        }
        
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #f3f4f6;
            border-color: #d1d5db;
            padding: 2px 8px;
            border-radius: 3px;
            margin-top: 5px;
        }
        
        /* Ajout bouton retour */
        .nav-buttons {
            text-align: center;
            margin-top: 20px;
        }
        
        .btn-return {
            background-color: #6b7280;
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 4px;
            display: inline-block;
            font-size: 14px;
            font-weight: 500;
        }
        
        .btn-return:hover {
            background-color: #4b5563;
            color: white;
        }
    </style>
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
</head>
<body>
    <div class="search-container">
        <div class="search-header">
            <h2><i class="fas fa-search me-2"></i> Recherche Avancée de Vulnérabilités</h2>
        </div>
        
        <div class="search-content">
            <form action="8-detail_produit.php" method="post">
                <!-- Informations de base -->
                <div class="form-section">
                    <h3 class="section-title">Informations de base</h3>
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label for="vendor" class="form-label">Vendor</label>
                            <select class="form-control select2-multiple" id="vendor" name="vendor[]" multiple="multiple">
                                <?php foreach ($vendors as $vendor): ?>
                                <option value="<?php echo htmlspecialchars($vendor); ?>"><?php echo htmlspecialchars($vendor); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="product" class="form-label">Produit</label>
                            <input type="text" class="form-control" id="product" name="product" placeholder="ex: panorama_m-200, toby-l200">
                        </div>
                    </div>
                    
                    <!-- Ajout de famille de produit et catégorie de produit -->
                    <div class="row g-2">
                        <div class="col-md-6 mb-2">
                            <label for="family" class="form-label">Famille de produit</label>
                            <select class="form-control select2-multiple" id="family" name="family[]" multiple="multiple">
                                <?php foreach ($families as $family): ?>
                                <option value="<?php echo htmlspecialchars($family); ?>"><?php echo htmlspecialchars($family); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="category" class="form-label">Catégorie de produit</label>
                            <select class="form-control select2-multiple" id="category" name="category[]" multiple="multiple">
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-md-4 mb-2">
                            <label for="cve_id" class="form-label">Identifiant CVE</label>
                            <input type="text" class="form-control" id="cve_id" name="cve_id" placeholder="ex: CVE-2023-0007">
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="fix_time" class="form-label">Temps de correction (jours)</label>
                            <div class="score-range">
                                <input type="number" class="form-control" id="fix_time_min" name="fix_time_min" placeholder="Min" min="0">
                                <span>à</span>
                                <input type="number" class="form-control" id="fix_time_max" name="fix_time_max" placeholder="Max" min="0">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="cvss_score" class="form-label">Score CVSS</label>
                            <div class="score-range">
                                <input type="number" class="form-control" id="cvss_score_min" name="cvss_score_min" placeholder="Min" min="0" max="10" step="0.1">
                                <span>à</span>
                                <input type="number" class="form-control" id="cvss_score_max" name="cvss_score_max" placeholder="Max" min="0" max="10" step="0.1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-2">
                        <div class="col-12 mb-2">
                            <label class="form-label">Sévérité</label>
                            <div class="severity-selector">
                                <div class="severity-item severity-critical" data-value="CRITICAL">Critique</div>
                                <div class="severity-item severity-high" data-value="HIGH">Élevée</div>
                                <div class="severity-item severity-medium" data-value="MEDIUM">Moyenne</div>
                                <div class="severity-item severity-low" data-value="LOW">Faible</div>
                            </div>
                            <input type="hidden" name="severity" id="severity_input" value="">
                        </div>
                    </div>
                </div>
                
                <!-- Catégories de vulnérabilités CWE -->
                <div class="form-section">
                    <h3 class="section-title">Catégories de vulnérabilités</h3>
                    <div class="categories-grid">
                        <?php
                        $categoriesCWE = [
                            'Cross Site Scripting' => 'Cross Site Scripting',
                            'Exécution de code' => 'Exécution de code',
                            'Validation insuffisante' => 'Validation insuffisante',
                            'SSRF' => 'SSRF',
                            'Corruption de mémoire' => 'Corruption de mémoire',
                            'Overflow' => 'Overflow',
                            'Fuite d\'informations' => 'Fuite d\'informations',
                            'Déni de service' => 'Déni de service',
                            'Directory Traversal' => 'Directory Traversal',
                            'CSRF' => 'CSRF',
                            'XXE Injection' => 'XXE Injection',
                            'Open Redirect' => 'Open Redirect',
                            'Injection SQL' => 'Injection SQL',
                            'File Inclusion' => 'File Inclusion',
                            'Élévation de privilèges' => 'Élévation de privilèges',
                            'Bypass de sécurité' => 'Bypass de sécurité'
                        ];
                        
                        foreach ($categoriesCWE as $value => $label):
                        ?>
                        <label class="category-item">
                            <input type="checkbox" name="cwe_category[]" value="<?php echo htmlspecialchars($value); ?>">
                            <span class="checkmark"></span>
                            <?php echo htmlspecialchars($label); ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Boutons de soumission -->
                <div class="form-section text-end">
                    <button type="reset" class="btn-reset me-2">
                        <i class="fas fa-undo me-1"></i> Réinitialiser
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-search me-1"></i> Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="nav-buttons">
        <a href="../0-Accuille.php" class="btn-return">
            <i class="fas fa-home me-2"></i>Retour à l'accueil
        </a>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialisation de Select2 pour les sélecteurs multiples
            $('.select2-multiple').select2({
                placeholder: "Sélectionnez une ou plusieurs options",
                allowClear: true,
                language: "fr"
            });
            
            // Gestion des sélecteurs de sévérité
            $('.severity-item').click(function() {
                $('.severity-item').removeClass('active');
                $(this).addClass('active');
                $('#severity_input').val($(this).data('value'));
            });
            
            // Pour la réinitialisation des select2 lors du reset du formulaire
            $("button[type='reset']").click(function() {
                setTimeout(function() {
                    $(".select2-multiple").val(null).trigger('change');
                    $('.severity-item').removeClass('active');
                    $('#severity_input').val('');
                }, 10);
            });
        });
    </script>
</body>
</html>