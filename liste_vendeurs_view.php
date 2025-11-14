<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Liste des Vendeurs et Produits</title>
  <!-- Inclusion de Bootstrap pour un affichage propre -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container mt-5">
    <h1 class="mb-4">Liste des Vendeurs et Produits</h1>
    <table class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Nom du Vendeur</th>
          <th>Produits</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Nom du fichier CSV contenant la liste des vendeurs
        $fichierVendeurs = '/Users/abderaoufbouhali/PycharmProjects/Mémoire/results/4-vendeurs/h/liste_vendeur_h_solo-nist.csv';

        // Vérifier si le fichier existe
        if (file_exists($fichierVendeurs)) {
            // Ouvrir le fichier en lecture
            if (($handle = fopen($fichierVendeurs, 'r')) !== false) {
                $firstLine = true;
                while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== false) {
                    // On ignore la première ligne (entête) si besoin
                    if ($firstLine) {
                        $firstLine = false;
                        continue;
                    }
                    
                    // Nom du vendeur
                    $nomVendeur = htmlspecialchars($data[0]);

                    // Chemin du fichier CSV des produits correspondant au vendeur
                    $fichierProduits = $nomVendeur . '.csv';

                    // Vérifier si le fichier de produits existe
                    $produits = [];
                    if (file_exists($fichierProduits)) {
                        if (($prodHandle = fopen($fichierProduits, 'r')) !== false) {
                            // Lire tous les produits du fichier
                            while (($prodData = fgetcsv($prodHandle, 1000, ",", '"', "\\")) !== false) {
                                $produits[] = htmlspecialchars($prodData[0]);
                            }
                            fclose($prodHandle);
                        }
                    }

                    // Affichage du vendeur et de ses produits sous forme de liste déroulante
                    echo '<tr>';
                    echo '<td>' . $nomVendeur . '</td>';
                    echo '<td>';
                    if (count($produits) > 0) {
                        // Liste déroulante des produits
                        echo '<select class="form-select">';
                        echo '<option value="">Sélectionner un produit</option>';
                        foreach ($produits as $produit) {
                            echo '<option value="' . $produit . '">' . $produit . '</option>';
                        }
                        echo '</select>';
                    } else {
                        echo 'Aucun produit';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                fclose($handle);
            } else {
                echo '<tr><td colspan="2" class="text-danger">Erreur lors de l\'ouverture du fichier des vendeurs.</td></tr>';
            }
        } else {
            echo '<tr><td colspan="2" class="text-danger">Le fichier des vendeurs n\'existe pas.</td></tr>';
        }
        ?>
      </tbody>
    </table>
  </div>
</body>
</html>