<?php
header('Content-Type: text/html; charset=utf-8');

$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 1;

try {
    // Récupérer le XML de l'emploi du temps
    $xml_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/generer_emploi_xml.php?classe_id=" . $classe_id;
    
    // Essayer d'abord avec l'URL complète
    $xml_content = @file_get_contents($xml_url);
    
    // Si ça ne marche pas, essayer en local
    if ($xml_content === false) {
        $xml_content = @file_get_contents("generer_emploi_xml.php?classe_id=" . $classe_id);
    }
    
    // Si ça ne marche pas encore, générer le XML directement
    if ($xml_content === false) {
        // Inclusion directe du script de génération XML
        ob_start();
        include 'generer_emploi_xml.php';
        $xml_content = ob_get_clean();
    }
    
    if (empty($xml_content)) {
        throw new Exception("Impossible de récupérer le XML de l'emploi du temps");
    }
    
    // Charger le document XML
    $xml_doc = new DOMDocument();
    $xml_doc->loadXML($xml_content);
    
    // Charger la feuille XSLT
    $xsl_doc = new DOMDocument();
    
    // Essayer de charger le fichier XSLT
    if (file_exists('../xsl/emploi_transform.xsl')) {
        $xsl_doc->load('../xsl/emploi_transform.xsl');
    } else {
        // Si le fichier n'existe pas, utiliser la feuille XSLT intégrée
        $xsl_content = '<?xml version="1.0" encoding="UTF-8"?>
        <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
            <xsl:output method="html" indent="yes" encoding="UTF-8" doctype-public="-//W3C//DTD HTML 4.01//EN"/>
            
            <xsl:template match="/">
                <html lang="fr">
                    <head>
                        <meta charset="UTF-8"/>
                        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                        <title>Emploi du temps - <xsl:value-of select="emploi/@classe"/></title>
                        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
                        <style>
                            body { 
                                background: linear-gradient(135deg, #278cf1 0%, #012d54 100%);
                                min-height: 100vh;
                                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                                padding: 20px;
                            }
                            .container {
                                background: rgba(255, 255, 255, 0.95);
                                backdrop-filter: blur(15px);
                                border-radius: 25px;
                                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
                                padding: 40px;
                                max-width: 1200px;
                            }
                            .page-header {
                                background: linear-gradient(45deg, #007bff, #0056b3);
                                color: white;
                                padding: 30px;
                                border-radius: 20px;
                                margin-bottom: 40px;
                                text-align: center;
                                box-shadow: 0 10px 30px rgba(0, 123, 255, 0.3);
                            }
                            .jour-section {
                                margin-bottom: 30px;
                                border-radius: 15px;
                                overflow: hidden;
                                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                                background: white;
                            }
                            .jour-header {
                                background: linear-gradient(45deg, #28a745, #1e7e34);
                                color: white;
                                padding: 20px;
                                font-weight: bold;
                                font-size: 1.3rem;
                                text-align: center;
                            }
                            .seance {
                                padding: 20px;
                                border-bottom: 1px solid #f1f3f4;
                            }
                            .seance:last-child {
                                border-bottom: none;
                            }
                            .horaire {
                                font-weight: bold;
                                color: #007bff;
                                font-size: 1.2rem;
                            }
                            .module-badge {
                                background: #28a745;
                                color: white;
                                padding: 8px 16px;
                                border-radius: 20px;
                                font-size: 0.9rem;
                                font-weight: 500;
                            }
                            .stats {
                                background: linear-gradient(45deg, #17a2b8, #138496);
                                color: white;
                                padding: 20px;
                                border-radius: 15px;
                                margin-bottom: 30px;
                                text-align: center;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="page-header">
                                <h1 class="display-4 mb-3">Emploi du Temps</h1>
                                <h2 class="mb-0">Classe <xsl:value-of select="emploi/@classe"/></h2>
                                <p class="mb-0 mt-2">Généré par transformation XSLT</p>
                            </div>
                            
                            <div class="stats">
                                <h3><xsl:value-of select="count(emploi/seance)"/></h3>
                                <p class="mb-0">Séances programmées cette semaine</p>
                            </div>
                            
                            <xsl:choose>
                                <xsl:when test="count(emploi/seance) > 0">
                                    <xsl:for-each select="emploi/seance[not(@jour = preceding-sibling::seance/@jour)]">
                                        <xsl:variable name="jour" select="@jour"/>
                                        <div class="jour-section">
                                            <div class="jour-header">
                                                <xsl:value-of select="@jour"/>
                                                (<xsl:value-of select="count(//seance[@jour = $jour])"/> séance(s))
                                            </div>
                                            
                                            <xsl:for-each select="//seance[@jour = $jour]">
                                                <xsl:sort select="@debut"/>
                                                <div class="seance">
                                                    <div class="row align-items-center">
                                                        <div class="col-md-3">
                                                            <div class="horaire">
                                                                <xsl:value-of select="@debut"/> - <xsl:value-of select="@fin"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <span class="module-badge">
                                                                <xsl:value-of select="@module"/>
                                                            </span>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <xsl:value-of select="@prof"/>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <xsl:value-of select="@salle"/>
                                                        </div>
                                                    </div>
                                                </div>
                                            </xsl:for-each>
                                        </div>
                                    </xsl:for-each>
                                </xsl:when>
                                <xsl:otherwise>
                                    <div class="text-center py-5">
                                        <h4>Aucune séance programmée</h4>
                                        <p>Il n\'y a actuellement aucune séance dans l\'emploi du temps de cette classe.</p>
                                    </div>
                                </xsl:otherwise>
                            </xsl:choose>
                            
                            <div class="text-center mt-5">
                                <small class="text-muted">
                                    Document généré automatiquement via transformation XSLT
                                </small>
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
        <title>Erreur Transformation XSLT</title>
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
                                <li>Vérifiez que le fichier <code>generer_emploi_xml.php</code> existe et fonctionne</li>
                                <li>Vérifiez que l'extension PHP XSL est installée</li>
                                <li>Assurez-vous que la classe ID <?php echo $classe_id; ?> existe dans la base</li>
                            </ul>
                            
                            <div class="mt-4">
                                <a href="generer_emploi_xml.php?classe_id=<?php echo $classe_id; ?>" 
                                   class="btn btn-primary" target="_blank">
                                    <i class="fas fa-code"></i> Voir XML source
                                </a>
                                <button onclick="history.back()" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </button>
                                <a href="transform_emploi_xslt.php?classe_id=<?php echo $classe_id; ?>" 
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