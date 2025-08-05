<?php
header('Content-Type: application/xml; charset=utf-8');

// Configuration base de données
$host = 'localhost';
$dbname = 'emploi_du_temps';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    // En cas d'erreur de connexion, générer un XML d'exemple
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<erreur>Connexion base de données échouée: ' . htmlspecialchars($e->getMessage()) . '</erreur>';
    exit;
}

// Récupérer l'ID de classe (par défaut 1 si non spécifié)
$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 1;

try {
    // 1. Récupérer les informations de la classe
    $sql_classe = "SELECT 
        c.id_classe,
        c.niveau,
        f.nom_filiere
    FROM classes c
    JOIN filieres f ON c.filiere_id = f.id_filiere
    WHERE c.id_classe = ?";
    
    $stmt_classe = $pdo->prepare($sql_classe);
    $stmt_classe->execute([$classe_id]);
    $classe = $stmt_classe->fetch(PDO::FETCH_ASSOC);
    
    if (!$classe) {
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<erreur>Classe avec ID ' . $classe_id . ' non trouvée</erreur>';
        exit;
    }
    
    // 2. Récupérer les étudiants de cette classe
    $sql_etudiants = "SELECT 
        e.num_inscription,
        e.nom_et,
        e.prenom_et
    FROM etudiants e
    WHERE e.classe_id = ?
    ORDER BY e.nom_et, e.prenom_et";
    
    $stmt_etudiants = $pdo->prepare($sql_etudiants);
    $stmt_etudiants->execute([$classe_id]);
    $etudiants = $stmt_etudiants->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Récupérer les modules enseignés à cette classe
    $sql_modules = "SELECT DISTINCT
        m.id_module,
        m.nom_module,
        m.description
    FROM modules m
    JOIN cours c ON m.id_module = c.module_id
    WHERE c.classe_id = ?
    ORDER BY m.nom_module";
    
    $stmt_modules = $pdo->prepare($sql_modules);
    $stmt_modules->execute([$classe_id]);
    $modules = $stmt_modules->fetchAll(PDO::FETCH_ASSOC);
    
    // 4. Générer le XML
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<classe filiere="' . htmlspecialchars($classe['nom_filiere']) . '" niveau="' . htmlspecialchars($classe['niveau']) . '">' . "\n";
    
    // Section étudiants
    echo '  <etudiants>' . "\n";
    if (!empty($etudiants)) {
        foreach ($etudiants as $etudiant) {
            echo '    <etudiant ';
            echo 'numInscription="' . htmlspecialchars($etudiant['num_inscription']) . '" ';
            echo 'nom="' . htmlspecialchars($etudiant['nom_et']) . '" ';
            echo 'prenom="' . htmlspecialchars($etudiant['prenom_et']) . '"';
            echo '/>' . "\n";
        }
    } else {
        echo '    <!-- Aucun étudiant inscrit dans cette classe -->' . "\n";
    }
    echo '  </etudiants>' . "\n";
    
    // Section modules
    echo '  <modules>' . "\n";
    if (!empty($modules)) {
        foreach ($modules as $module) {
            echo '    <module ';
            echo 'idModule="' . htmlspecialchars($module['id_module']) . '" ';
            echo 'nomModule="' . htmlspecialchars($module['nom_module']) . '"';
            if (!empty($module['description'])) {
                echo ' description="' . htmlspecialchars($module['description']) . '"';
            }
            echo '/>' . "\n";
        }
    } else {
        echo '    <!-- Aucun module assigné à cette classe -->' . "\n";
    }
    echo '  </modules>' . "\n";
    
    // Informations supplémentaires (optionnel)
    echo '  <statistiques>' . "\n";
    echo '    <nbEtudiants>' . count($etudiants) . '</nbEtudiants>' . "\n";
    echo '    <nbModules>' . count($modules) . '</nbModules>' . "\n";
    echo '    <dateGeneration>' . date('Y-m-d H:i:s') . '</dateGeneration>' . "\n";
    echo '  </statistiques>' . "\n";
    
    echo '</classe>';
    
} catch(PDOException $e) {
    // Erreur SQL
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<erreur>Erreur SQL: ' . htmlspecialchars($e->getMessage()) . '</erreur>';
} catch(Exception $e) {
    // Autres erreurs
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<erreur>Erreur générale: ' . htmlspecialchars($e->getMessage()) . '</erreur>';
}
?>