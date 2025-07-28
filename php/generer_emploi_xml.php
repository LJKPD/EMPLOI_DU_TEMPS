<?php
header('Content-Type: application/xml; charset=utf-8');

// Configuration base de données
$host = 'localhost';
$dbname = 'emploi_du_temps';
$username = 'root';
$password = '';

try {
    // Test de connexion
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // En cas d'erreur, afficher un XML d'erreur
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<erreur>Connexion base de données échouée: ' . htmlspecialchars($e->getMessage()) . '</erreur>';
    exit;
}

// Récupérer l'ID de classe
$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 1;

try {
    // Requête pour l'emploi du temps
    $sql = "SELECT 
        c.jour,
        c.heure_debut,
        c.heure_fin,
        p.nom_prof,
        m.nom_module,
        s.nom_salle,
        f.nom_filiere,
        cl.niveau
    FROM cours c
    JOIN professeurs p ON c.prof_id = p.id_prof
    JOIN modules m ON c.module_id = m.id_module
    JOIN salles s ON c.salle_id = s.id_salle
    JOIN classes cl ON c.classe_id = cl.id_classe
    JOIN filieres f ON cl.filiere_id = f.id_filiere
    WHERE c.classe_id = ?
    ORDER BY 
        CASE c.jour 
            WHEN 'Lundi' THEN 1
            WHEN 'Mardi' THEN 2
            WHEN 'Mercredi' THEN 3
            WHEN 'Jeudi' THEN 4
            WHEN 'Vendredi' THEN 5
        END,
        c.heure_debut";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$classe_id]);
    $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Déterminer le nom de la classe
    if (!empty($seances)) {
        $nom_classe = $seances[0]['nom_filiere'] . $seances[0]['niveau'];
    } else {
        $nom_classe = "Classe" . $classe_id;
    }

    // Génération du XML
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<emploi classe="' . htmlspecialchars($nom_classe) . '">' . "\n";

    foreach ($seances as $seance) {
        echo '  <seance ';
        echo 'jour="' . htmlspecialchars($seance['jour']) . '" ';
        echo 'debut="' . htmlspecialchars(substr($seance['heure_debut'], 0, 5)) . '" ';
        echo 'fin="' . htmlspecialchars(substr($seance['heure_fin'], 0, 5)) . '" ';
        echo 'prof="' . htmlspecialchars($seance['nom_prof']) . '" ';
        echo 'module="' . htmlspecialchars($seance['nom_module']) . '" ';
        echo 'salle="' . htmlspecialchars($seance['nom_salle']) . '"';
        echo '/>' . "\n";
    }

    echo '</emploi>';

} catch(PDOException $e) {
    // Erreur SQL
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<erreur>Erreur SQL: ' . htmlspecialchars($e->getMessage()) . '</erreur>';
}
?>