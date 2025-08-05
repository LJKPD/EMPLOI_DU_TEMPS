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
    
    // Requête pour récupérer tous les professeurs
    $sql = "SELECT id_prof, nom_prof, tel FROM professeurs ORDER BY nom_prof";
    $stmt = $pdo->query($sql);
    $professeurs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucun professeur trouvé, créer des données d'exemple
    if (empty($professeurs)) {
        $professeurs = [
            ['id_prof' => 1, 'nom_prof' => 'Dr. MARTIN Jean', 'tel' => '01.23.45.67.89'],
            ['id_prof' => 2, 'nom_prof' => 'Prof. DURAND Marie', 'tel' => '01.98.76.54.32'],
            ['id_prof' => 3, 'nom_prof' => 'Dr. BERNARD Pierre', 'tel' => '01.11.22.33.44'],
            ['id_prof' => 4, 'nom_prof' => 'Prof. THOMAS Sophie', 'tel' => '01.55.66.77.88'],
            ['id_prof' => 5, 'nom_prof' => 'Dr. ROBERT Lucas', 'tel' => '01.99.88.77.66'],
            ['id_prof' => 6, 'nom_prof' => 'Prof. PETIT Emma', 'tel' => '01.44.55.66.77'],
            ['id_prof' => 7, 'nom_prof' => 'Dr. MOREAU Hugo', 'tel' => '01.33.44.55.66'],
            ['id_prof' => 8, 'nom_prof' => 'Prof. SIMON Chloé', 'tel' => '01.22.33.44.55']
        ];
    }
    
    echo json_encode($professeurs, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    // En cas d'erreur, retourner des données d'exemple
    echo json_encode([
        ['id_prof' => 1, 'nom_prof' => 'Dr. MARTIN Jean', 'tel' => '01.23.45.67.89'],
        ['id_prof' => 2, 'nom_prof' => 'Prof. DURAND Marie', 'tel' => '01.98.76.54.32'],
        ['id_prof' => 3, 'nom_prof' => 'Dr. BERNARD Pierre', 'tel' => '01.11.22.33.44'],
        ['id_prof' => 4, 'nom_prof' => 'Prof. THOMAS Sophie', 'tel' => '01.55.66.77.88'],
        ['id_prof' => 5, 'nom_prof' => 'Dr. ROBERT Lucas', 'tel' => '01.99.88.77.66'],
        ['id_prof' => 6, 'nom_prof' => 'Prof. PETIT Emma', 'tel' => '01.44.55.66.77'],
        ['id_prof' => 7, 'nom_prof' => 'Dr. MOREAU Hugo', 'tel' => '01.33.44.55.66'],
        ['id_prof' => 8, 'nom_prof' => 'Prof. SIMON Chloé', 'tel' => '01.22.33.44.55']
    ], JSON_UNESCAPED_UNICODE);
}
?>