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
    
    // Requête pour calculer les charges horaires par professeur
    $sql = "SELECT 
        p.nom_prof,
        COUNT(c.id) as nb_seances,
        SUM(TIME_TO_SEC(TIMEDIFF(c.heure_fin, c.heure_debut)) / 3600) as total_heures,
        GROUP_CONCAT(DISTINCT m.nom_module SEPARATOR ', ') as modules
    FROM professeurs p
    LEFT JOIN cours c ON p.id_prof = c.prof_id
    LEFT JOIN modules m ON c.module_id = m.id_module
    GROUP BY p.id_prof, p.nom_prof
    ORDER BY total_heures DESC";
    
    $stmt = $pdo->query($sql);
    $charges = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Préparer les données pour Chart.js
    $labels = [];
    $data = [];
    $colors = [];
    $nb_seances = [];
    $modules_list = [];
    
    // Palette de couleurs
    $color_palette = [
        'rgba(255, 99, 132, 0.8)',   // Rouge
        'rgba(54, 162, 235, 0.8)',   // Bleu
        'rgba(255, 205, 86, 0.8)',   // Jaune
        'rgba(75, 192, 192, 0.8)',   // Vert turquoise
        'rgba(153, 102, 255, 0.8)',  // Violet
        'rgba(255, 159, 64, 0.8)',   // Orange
        'rgba(199, 199, 199, 0.8)',  // Gris
        'rgba(83, 102, 255, 0.8)',   // Bleu indigo
        'rgba(255, 99, 255, 0.8)',   // Rose
        'rgba(99, 255, 132, 0.8)'    // Vert clair
    ];
    
    foreach ($charges as $index => $charge) {
        $labels[] = $charge['nom_prof'];
        $data[] = round($charge['total_heures'] ?? 0, 2);
        $colors[] = $color_palette[$index % count($color_palette)];
        $nb_seances[] = $charge['nb_seances'] ?? 0;
        $modules_list[] = $charge['modules'] ?? 'Aucun module assigné';
    }
    
    // Calculer quelques statistiques
    $total_heures_general = array_sum($data);
    $moyenne_heures = count($data) > 0 ? round($total_heures_general / count($data), 2) : 0;
    $prof_plus_charge = count($labels) > 0 ? $labels[0] : 'N/A';
    $heures_max = count($data) > 0 ? max($data) : 0;
    
    // Structure de réponse
    $response = [
        'success' => true,
        'data' => [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Heures d\'enseignement par semaine',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => array_map(function($color) {
                        return str_replace('0.8', '1', $color);
                    }, $colors),
                    'borderWidth' => 2
                ]
            ]
        ],

        'details' => [
            'nb_seances' => $nb_seances,
            'modules' => $modules_list
        ],

        'statistiques' => [
            'total_heures' => $total_heures_general,
            'moyenne_heures' => $moyenne_heures,
            'nb_professeurs' => count($labels),
            'prof_plus_charge' => $prof_plus_charge,
            'heures_max' => $heures_max
        ],

        'message' => count($charges) > 0 ? 
            'Données des charges horaires récupérées avec succès' : 
            'Aucune charge horaire trouvée'
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    
} catch(PDOException $e) {
    // Gestion des erreurs de base de données
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur base de données',
        'message' => $e->getMessage(),
        'debug' => 'Vérifiez la connexion à la base de données et la structure des tables'
    ], JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    // Gestion des autres erreurs
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>