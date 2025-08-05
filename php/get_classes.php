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
    
    // Requête pour récupérer les classes avec leurs filières
    $sql = "SELECT c.id_classe, c.niveau, f.nom_filiere 
            FROM classes c 
            JOIN filieres f ON c.filiere_id = f.id_filiere 
            ORDER BY f.nom_filiere, c.niveau";
    
    $stmt = $pdo->query($sql);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucune classe trouvée, créer des données d'exemple
    if (empty($classes)) {
        $classes = [
            ['id_classe' => 1, 'niveau' => '1', 'nom_filiere' => 'INFO'],
            ['id_classe' => 2, 'niveau' => '2', 'nom_filiere' => 'INFO'], 
            ['id_classe' => 3, 'niveau' => '3', 'nom_filiere' => 'INFO'],
            ['id_classe' => 4, 'niveau' => '1', 'nom_filiere' => 'SRI'],
            ['id_classe' => 5, 'niveau' => '2', 'nom_filiere' => 'SRI'],
            ['id_classe' => 6, 'niveau' => '1', 'nom_filiere' => 'GL'],
            ['id_classe' => 7, 'niveau' => '2', 'nom_filiere' => 'GL'],
            ['id_classe' => 8, 'niveau' => '1', 'nom_filiere' => 'CCA']
        ];
    }
    
    // Retourner directement le tableau (format simplifié)
    echo json_encode($classes, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    // En cas d'erreur, retourner des données d'exemple
    echo json_encode([
        ['id_classe' => 1, 'niveau' => '1', 'nom_filiere' => 'INFO'],
        ['id_classe' => 2, 'niveau' => '2', 'nom_filiere' => 'INFO'], 
        ['id_classe' => 3, 'niveau' => '3', 'nom_filiere' => 'INFO'],
        ['id_classe' => 4, 'niveau' => '1', 'nom_filiere' => 'SRI'],
        ['id_classe' => 5, 'niveau' => '2', 'nom_filiere' => 'SRI'],
        ['id_classe' => 6, 'niveau' => '1', 'nom_filiere' => 'GL'],
        ['id_classe' => 7, 'niveau' => '2', 'nom_filiere' => 'GL'],
        ['id_classe' => 8, 'niveau' => '1', 'nom_filiere' => 'CCA']
    ], JSON_UNESCAPED_UNICODE);
}
?>