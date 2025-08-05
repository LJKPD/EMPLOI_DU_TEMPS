<?php
header('Content-Type: application/json; charset=utf-8');

// Configuration base de données
$host = 'localhost';
$dbname = 'emploi_du_temps';
$username = 'root';
$password = '';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Requête pour récupérer toutes les salles
    $sql = "SELECT id_salle, nom_salle FROM salles ORDER BY nom_salle";
    $stmt = $pdo->query($sql);
    $salles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucune salle trouvée, créer des données d'exemple
    if (empty($salles)) {
        $salles = [
            ['id_salle' => 1, 'nom_salle' => 'Amphi A'],
            ['id_salle' => 2, 'nom_salle' => 'Salle B101'],
            ['id_salle' => 3, 'nom_salle' => 'Lab Info 1'],
            ['id_salle' => 4, 'nom_salle' => 'Lab Info 2'],
            ['id_salle' => 5, 'nom_salle' => 'Salle C203'],
            ['id_salle' => 6, 'nom_salle' => 'Amphi B'],
            ['id_salle' => 7, 'nom_salle' => 'Lab Réseaux']
        ];
    }
    
    echo json_encode($salles, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    // En cas d'erreur, retourner des données d'exemple
    echo json_encode([
        ['id_salle' => 1, 'nom_salle' => 'Amphi A'],
        ['id_salle' => 2, 'nom_salle' => 'Salle B101'],
        ['id_salle' => 3, 'nom_salle' => 'Lab Info 1'],
        ['id_salle' => 4, 'nom_salle' => 'Lab Info 2'],
        ['id_salle' => 5, 'nom_salle' => 'Salle C203'],
        ['id_salle' => 6, 'nom_salle' => 'Amphi B'],
        ['id_salle' => 7, 'nom_salle' => 'Lab Réseaux']
    ], JSON_UNESCAPED_UNICODE);
}
?>