<?php
header('Content-Type: application/json; charset=utf-8');

// Configuration base de données
$host = 'localhost';
$dbname = 'emploi_du_temps';
$username = 'root';
$password = '';

try {
    // Test de connexion
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Vérifier si la table classes existe et contient des données
    $test_classes = $pdo->query("SELECT COUNT(*) as nb FROM classes");
    $nb_classes = $test_classes->fetch(PDO::FETCH_ASSOC)['nb'];
    
    if ($nb_classes == 0) {
        // Aucune classe dans la table
        echo json_encode([
            'error' => 'Aucune classe trouvée dans la base de données',
            'debug' => 'La table classes est vide. Avez-vous exécuté le script de données complémentaires ?'
        ]);
        exit;
    }
    
    // Requête pour récupérer les classes avec leurs filières
    $sql = "SELECT c.id_classe, c.niveau, f.nom_filiere 
            FROM classes c 
            JOIN filieres f ON c.filiere_id = f.id_filiere 
            ORDER BY f.nom_filiere, c.niveau";
    
    $stmt = $pdo->query($sql);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Vérifier le résultat
    if (empty($classes)) {
        echo json_encode([
            'error' => 'Problème de jointure entre classes et filieres',
            'debug' => 'Classes trouvées: ' . $nb_classes . ' mais jointure échoue',
            'suggestion' => 'Vérifiez que les filiere_id dans classes correspondent aux id_filiere dans filieres'
        ]);
    } else {
        // Succès : retourner les classes
        echo json_encode([
            'success' => true,
            'nb_classes' => count($classes),
            'classes' => $classes,
            'debug' => 'Classes chargées avec succès'
        ]);
    }
    
} catch(PDOException $e) {
    // Erreur de connexion ou SQL
    echo json_encode([
        'error' => 'Erreur base de données: ' . $e->getMessage(),
        'debug' => 'Vérifiez vos paramètres de connexion'
    ]);
}
?>