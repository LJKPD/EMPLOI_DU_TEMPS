<?php
header('Content-Type: application/json; charset=utf-8');

// Configuration base de données
$host = 'localhost';
$dbname = 'emploi_du_temps';
$username = 'root';
$password = '';

// Vérifier que c'est une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Méthode non autorisée. Utilisez POST.'
    ]);
    exit;
}

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Connexion base de données échouée: ' . $e->getMessage()
    ]);
    exit;
}

try {
    // RÉCUPÉRATION ET VALIDATION DES DONNÉES
    $classe_id = isset($_POST['classe_id']) ? (int)$_POST['classe_id'] : 0;
    $prof_id = isset($_POST['prof_id']) ? (int)$_POST['prof_id'] : 0;
    $module_id = isset($_POST['module_id']) ? (int)$_POST['module_id'] : 0;
    $salle_id = isset($_POST['salle_id']) ? (int)$_POST['salle_id'] : 0;
    $jour = isset($_POST['jour']) ? trim($_POST['jour']) : '';
    $heure_debut = isset($_POST['heure_debut']) ? trim($_POST['heure_debut']) : '';
    $heure_fin = isset($_POST['heure_fin']) ? trim($_POST['heure_fin']) : '';
    
    // Validation des données obligatoires
    $erreurs = [];
    
    if ($classe_id <= 0) $erreurs[] = 'Classe non sélectionnée';
    if ($prof_id <= 0) $erreurs[] = 'Professeur non sélectionné';
    if ($module_id <= 0) $erreurs[] = 'Module non sélectionné';
    if ($salle_id <= 0) $erreurs[] = 'Salle non sélectionnée';
    if (empty($jour)) $erreurs[] = 'Jour non sélectionné';
    if (empty($heure_debut)) $erreurs[] = 'Heure de début manquante';
    if (empty($heure_fin)) $erreurs[] = 'Heure de fin manquante';
    
    // Validation des horaires
    if (!empty($heure_debut) && !empty($heure_fin)) {
        if ($heure_debut >= $heure_fin) {
            $erreurs[] = 'L\'heure de fin doit être postérieure à l\'heure de début';
        }
    }
    
    // Validation du jour
    $jours_autorises = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
    if (!in_array($jour, $jours_autorises)) {
        $erreurs[] = 'Jour non valide';
    }
    
    // Validation des horaires selon le jour
    if (!empty($heure_debut) && !empty($heure_fin)) {
        // Vérifier que les horaires sont dans les plages autorisées
        if ($heure_debut < '07:00') {
            $erreurs[] = 'Heure de début trop tôt (minimum 07h00)';
        }
        
        // Vérification spécifique pour le samedi
        if ($jour === 'Samedi') {
            if ($heure_debut > '17:30') {
                $erreurs[] = 'Heure de début trop tard pour le samedi (maximum 17h30)';
            }
            if ($heure_fin > '17:30') {
                $erreurs[] = 'Heure de fin trop tard pour le samedi (maximum 17h30)';
            }
        } else {
            // Autres jours de la semaine
            if ($heure_debut > '20:00') {
                $erreurs[] = 'Heure de début trop tard (maximum 20h00)';
            }
            if ($heure_fin > '20:00') {
                $erreurs[] = 'Heure de fin trop tard (maximum 20h00)';
            }
        }
    }
    
    if (!empty($erreurs)) {
        echo json_encode([
            'success' => false,
            'error' => 'Données invalides: ' . implode(', ', $erreurs)
        ]);
        exit;
    }
    
    // VÉRIFICATION DE L'EXISTENCE DES ENTITÉS
    // Vérifier que la classe existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE id_classe = ?");
    $stmt->execute([$classe_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode([
            'success' => false,
            'error' => "Classe avec ID $classe_id n'existe pas"
        ]);
        exit;
    }
    
    // Vérifier que le professeur existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM professeurs WHERE id_prof = ?");
    $stmt->execute([$prof_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode([
            'success' => false,
            'error' => "Professeur avec ID $prof_id n'existe pas"
        ]);
        exit;
    }
    
    // Vérifier que le module existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM modules WHERE id_module = ?");
    $stmt->execute([$module_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode([
            'success' => false,
            'error' => "Module avec ID $module_id n'existe pas"
        ]);
        exit;
    }
    
    // Vérifier que la salle existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM salles WHERE id_salle = ?");
    $stmt->execute([$salle_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode([
            'success' => false,
            'error' => "Salle avec ID $salle_id n'existe pas"
        ]);
        exit;
    }
    
    // VÉRIFICATION DES CONFLITS (optionnel mais important)
    $conflits = [];
    
    // Conflit professeur
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM cours 
        WHERE prof_id = ? AND jour = ? 
        AND (
            (heure_debut <= ? AND heure_fin > ?) OR
            (heure_debut < ? AND heure_fin >= ?) OR
            (heure_debut >= ? AND heure_fin <= ?)
        )
    ");
    $stmt->execute([$prof_id, $jour, $heure_debut, $heure_debut, $heure_fin, $heure_fin, $heure_debut, $heure_fin]);
    if ($stmt->fetchColumn() > 0) {
        $conflits[] = 'Le professeur a déjà un cours à ces horaires';
    }
    
    // Conflit salle
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM cours 
        WHERE salle_id = ? AND jour = ? 
        AND (
            (heure_debut <= ? AND heure_fin > ?) OR
            (heure_debut < ? AND heure_fin >= ?) OR
            (heure_debut >= ? AND heure_fin <= ?)
        )
    ");
    $stmt->execute([$salle_id, $jour, $heure_debut, $heure_debut, $heure_fin, $heure_fin, $heure_debut, $heure_fin]);
    if ($stmt->fetchColumn() > 0) {
        $conflits[] = 'La salle est déjà occupée à ces horaires';
    }
    
    // Conflit classe
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM cours 
        WHERE classe_id = ? AND jour = ? 
        AND (
            (heure_debut <= ? AND heure_fin > ?) OR
            (heure_debut < ? AND heure_fin >= ?) OR
            (heure_debut >= ? AND heure_fin <= ?)
        )
    ");
    $stmt->execute([$classe_id, $jour, $heure_debut, $heure_debut, $heure_fin, $heure_fin, $heure_debut, $heure_fin]);
    if ($stmt->fetchColumn() > 0) {
        $conflits[] = 'La classe a déjà un cours à ces horaires';
    }
    
    if (!empty($conflits)) {
        echo json_encode([
            'success' => false,
            'error' => 'Conflit d\'horaires: ' . implode(', ', $conflits)
        ]);
        exit;
    }
    
    // INSERTION EN BASE DE DONNÉES
    $sql = "INSERT INTO cours (classe_id, prof_id, module_id, salle_id, jour, heure_debut, heure_fin) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([$classe_id, $prof_id, $module_id, $salle_id, $jour, $heure_debut, $heure_fin]);
    
    if ($result) {
        $seance_id = $pdo->lastInsertId();
        
        // RÉCUPÉRER LES DÉTAILS POUR CONFIRMATION
        $sql_details = "
            SELECT 
                CONCAT(f.nom_filiere, cl.niveau) as classe_nom,
                p.nom_prof,
                m.nom_module,
                s.nom_salle
            FROM cours c
            JOIN classes cl ON c.classe_id = cl.id_classe
            LEFT JOIN filieres f ON cl.filiere_id = f.id_filiere
            JOIN professeurs p ON c.prof_id = p.id_prof
            JOIN modules m ON c.module_id = m.id_module
            JOIN salles s ON c.salle_id = s.id_salle
            WHERE c.id = ?
        ";
        
        $stmt_details = $pdo->prepare($sql_details);
        $stmt_details->execute([$seance_id]);
        $details = $stmt_details->fetch(PDO::FETCH_ASSOC);
        
        // Réponse de succès
        echo json_encode([
            'success' => true,
            'message' => 'Séance ajoutée avec succès',
            'seance_id' => $seance_id,
            'details' => [
                'classe' => $details['classe_nom'] ?? "Classe $classe_id",
                'professeur' => $details['nom_prof'] ?? "Professeur $prof_id",
                'module' => $details['nom_module'] ?? "Module $module_id",
                'salle' => $details['nom_salle'] ?? "Salle $salle_id",
                'jour' => $jour,
                'horaire' => "$heure_debut - $heure_fin"
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Erreur lors de l\'insertion en base de données'
        ]);
    }
    
} catch(PDOException $e) {
    // Erreur SQL
    echo json_encode([
        'success' => false,
        'error' => 'Erreur base de données: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    // Autres erreurs
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur: ' . $e->getMessage()
    ]);
}
?>