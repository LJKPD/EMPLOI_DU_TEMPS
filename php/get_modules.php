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
    
    // Requête pour récupérer tous les modules
    $sql = "SELECT id_module, nom_module, description FROM modules ORDER BY nom_module";
    $stmt = $pdo->query($sql);
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Si aucun module trouvé, créer des données d'exemple
    if (empty($modules)) {
        $modules = [
            ['id_module' => 1, 'nom_module' => 'Programmation Web', 'description' => 'HTML, CSS, JavaScript, PHP'],
            ['id_module' => 2, 'nom_module' => 'Base de Données', 'description' => 'SQL, MySQL, PostgreSQL'],
            ['id_module' => 3, 'nom_module' => 'Algorithmes', 'description' => 'Structures de données et complexité'],
            ['id_module' => 4, 'nom_module' => 'Réseaux Informatiques', 'description' => 'TCP/IP, protocoles réseau'],
            ['id_module' => 5, 'nom_module' => 'Systèmes d\'Exploitation', 'description' => 'Linux, Windows, administration'],
            ['id_module' => 6, 'nom_module' => 'Génie Logiciel', 'description' => 'Méthodes agiles, UML, tests'],
            ['id_module' => 7, 'nom_module' => 'Intelligence Artificielle', 'description' => 'Machine Learning, IA'],
            ['id_module' => 8, 'nom_module' => 'Sécurité Informatique', 'description' => 'Cryptographie, cybersécurité'],
            ['id_module' => 9, 'nom_module' => 'Mathématiques Appliquées', 'description' => 'Statistiques, algèbre, analyse'],
            ['id_module' => 10, 'nom_module' => 'Anglais Technique', 'description' => 'Anglais professionnel IT']
        ];
    }
    
    echo json_encode($modules, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    // En cas d'erreur, retourner des données d'exemple
    echo json_encode([
        ['id_module' => 1, 'nom_module' => 'Programmation Web', 'description' => 'HTML, CSS, JavaScript, PHP'],
        ['id_module' => 2, 'nom_module' => 'Base de Données', 'description' => 'SQL, MySQL, PostgreSQL'],
        ['id_module' => 3, 'nom_module' => 'Algorithmes', 'description' => 'Structures de données et complexité'],
        ['id_module' => 4, 'nom_module' => 'Réseaux Informatiques', 'description' => 'TCP/IP, protocoles réseau'],
        ['id_module' => 5, 'nom_module' => 'Systèmes d\'Exploitation', 'description' => 'Linux, Windows, administration'],
        ['id_module' => 6, 'nom_module' => 'Génie Logiciel', 'description' => 'Méthodes agiles, UML, tests'],
        ['id_module' => 7, 'nom_module' => 'Intelligence Artificielle', 'description' => 'Machine Learning, IA'],
        ['id_module' => 8, 'nom_module' => 'Sécurité Informatique', 'description' => 'Cryptographie, cybersécurité'],
        ['id_module' => 9, 'nom_module' => 'Mathématiques Appliquées', 'description' => 'Statistiques, algèbre, analyse'],
        ['id_module' => 10, 'nom_module' => 'Anglais Technique', 'description' => 'Anglais professionnel IT']
    ], JSON_UNESCAPED_UNICODE);
}
?>