<?php
header('Content-Type: text/html; charset=utf-8');

$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 1;

try {
    // Récupérer le XML de la classe
    $xml_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/generer_classe_xml.php?classe_id=" . $classe_id;
    
    // Essayer d'abord avec l'URL complète
    $xml_content = @file_get_contents($xml_url);
    
    // Si ça ne marche pas, essayer en local
    if ($xml_content === false) {
        $xml_content = @file_get_contents("generer_classe_xml.php?classe_id=" . $classe_id);
    }
    
    // Si ça ne marche pas encore, générer le XML directement
    if ($xml_content === false) {
        ob_start();
        include 'generer_classe_xml.php';
        $xml_content = ob_get_clean();
    }
    
    if (empty($xml_content)) {
        throw new Exception("Impossible de récupérer le XML de la classe");
    }
    
    // Vérifier si c'est une erreur XML
    if (strpos($xml_content, '<erreur>') !== false) {
        // Extraire le message d'erreur
        preg_match('/<erreur>(.*?)<\/erreur>/', $xml_content, $matches);
        $error_message = isset($matches[1]) ? $matches[1] : 'Erreur inconnue';
        throw new Exception($error_message);
    }
    
    // Charger le document XML
    $xml_doc = new DOMDocument();
    if (!$xml_doc->loadXML($xml_content)) {
        throw new Exception("XML malformé reçu du serveur");
    }
    
    // Charger la feuille XSLT
    $xsl_doc = new DOMDocument();
    
    // Essayer de charger le fichier XSLT
    if (file_exists('../xsl/classe_transform.xsl')) {
        $xsl_doc->load('../xsl/classe_transform.xsl');
    } elseif (file_exists('xsl/classe_transform.xsl')) {
        $xsl_doc->load('xsl/classe_transform.xsl');
    } else {
        // Si le fichier n'existe pas, utiliser la feuille XSLT intégrée
        $xsl_content = '<?xml version="1.0" encoding="UTF-8"?>
        <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
            <xsl:output method="html" indent="yes" encoding="UTF-8"/>
            
            <xsl:template match="/">
                <html lang="fr">
                    <head>
                        <meta charset="UTF-8"/>
                        <title>Classe <xsl:value-of select="classe/@filiere"/><xsl:value-of select="classe/@niveau"/></title>
                        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
                        <style>
                            body { 
                                background: linear-gradient(135deg, #278cf1 0%, #012d54 100%);
                                min-height: 100vh;
                                padding: 20px;
                            }
                            .container {
                                background: white;
                                border-radius: 15px;
                                padding: 30px;
                                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <h1 class="text-center mb-4">
                                Classe <xsl:value-of select="classe/@filiere"/><xsl:value-of select="classe/@niveau"/>
                            </h1>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>Étudiants (<xsl:value-of select="count(classe/etudiants/etudiant)"/>)</h3>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>N° Inscription</th>
                                                <th>Nom</th>
                                                <th>Prénom</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <xsl:for-each select="classe/etudiants/etudiant">
                                                <tr>
                                                    <td><xsl:value-of select="@numInscription"/></td>
                                                    <td><xsl:value-of select="@nom"/></td>
                                                    <td><xsl:value-of select="@prenoms"/></td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="col-md-6">
                                    <h3>Modules (<xsl:value-of select="count(classe/modules/module)"/>)</h3>
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom du Module</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <xsl:for-each select="classe/modules/module">
                                                <tr>
                                                    <td><xsl:value-of select="@idModule"/></td>
                                                    <td><xsl:value-of select="@nomModule"/></td>
                                                </tr>
                                            </xsl:for-each>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </body>
                </html>
            </xsl:template>
        </xsl:stylesheet>';
        
        $xsl_doc->loadXML($xsl_content);
    }
    
    // Créer le processeur XSLT
    $processor = new XSLTProcessor();
    $processor->importStylesheet($xsl_doc);
    
    // Effectuer la transformation et afficher le résultat
    echo $processor->transformToXML($xml_doc);
    
} catch (Exception $e) {
    // Gestion d'erreur avec page HTML
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur Transformation XSLT - Classe</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #278cf1 0%, #012d54 100%);
                min-height: 100vh;
                padding: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                Erreur de transformation XSLT
                            </h4>
                        </div>
                        <div class="card-body">
                            <p><strong>Détail de l'erreur :</strong></p>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($e->getMessage()); ?>
                            </div>
                            
                            <p><strong>Solutions possibles :</strong></p>
                            <ul>
                                <li>Vérifiez que le fichier <code>generer_classe_xml.php</code> existe et fonctionne</li>
                                <li>Vérifiez que l'extension PHP XSL est installée</li>
                                <li>Assurez-vous que la classe ID <?php echo $classe_id; ?> existe dans la base</li>
                                <li>Vérifiez que les tables etudiants et modules contiennent des données</li>
                            </ul>
                            
                            <div class="mt-4">
                                <a href="generer_classe_xml.php?classe_id=<?php echo $classe_id; ?>" 
                                   class="btn btn-primary" target="_blank">
                                    <i class="fas fa-code"></i> Voir XML source
                                </a>
                                <button onclick="history.back()" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <a href="transform_classe_xslt.php?classe_id=<?php echo $classe_id; ?>" 
                                   class="btn btn-warning">
                                    <i class="fas fa-redo"></i> Réessayer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>