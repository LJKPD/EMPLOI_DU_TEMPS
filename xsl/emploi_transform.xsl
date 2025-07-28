<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8" doctype-public="-//W3C//DTD HTML 4.01//EN"/>
    
    <xsl:template match="/">
        <html lang="fr">
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <title>Emploi du temps - <xsl:value-of select="emploi/@classe"/></title>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet"/>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
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
                        transition: transform 0.3s ease;
                    }
                    .jour-section:hover {
                        transform: translateY(-5px);
                    }
                    .jour-header {
                        background: linear-gradient(45deg, #28a745, #1e7e34);
                        color: white;
                        padding: 20px;
                        font-weight: bold;
                        font-size: 1.3rem;
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                    }
                    .seance {
                        padding: 20px;
                        border-bottom: 1px solid #f1f3f4;
                        transition: all 0.3s ease;
                        position: relative;
                    }
                    .seance:hover {
                        background: #f8f9fa;
                        transform: translateX(10px);
                    }
                    .seance:last-child {
                        border-bottom: none;
                    }
                    .seance::before {
                        content: "";
                        position: absolute;
                        left: 0;
                        top: 0;
                        bottom: 0;
                        width: 4px;
                        background: linear-gradient(to bottom, #007bff, #0056b3);
                        transform: scaleY(0);
                        transition: transform 0.3s ease;
                    }
                    .seance:hover::before {
                        transform: scaleY(1);
                    }
                    .horaire {
                        font-weight: bold;
                        color: #007bff;
                        font-size: 1.2rem;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    }
                    .module-badge {
                        background: linear-gradient(45deg, #28a745, #1e7e34);
                        color: white;
                        padding: 8px 16px;
                        border-radius: 20px;
                        font-size: 0.9rem;
                        font-weight: 500;
                        display: inline-flex;
                        align-items: center;
                        gap: 6px;
                        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
                    }
                    .prof-info {
                        color: #6c757d;
                        font-style: italic;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        margin-top: 5px;
                    }
                    .salle-info {
                        color: #dc3545;
                        font-weight: 500;
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        margin-top: 5px;
                    }
                    .stats {
                        background: linear-gradient(45deg, #17a2b8, #138496);
                        color: white;
                        padding: 20px;
                        border-radius: 15px;
                        margin-bottom: 30px;
                        text-align: center;
                        box-shadow: 0 8px 25px rgba(23, 162, 184, 0.3);
                    }
                    .stats h3 {
                        font-size: 2.5rem;
                        margin-bottom: 5px;
                        font-weight: bold;
                    }
                    .empty-state {
                        text-align: center;
                        padding: 60px 20px;
                        color: #6c757d;
                    }
                    .empty-state i {
                        font-size: 4rem;
                        margin-bottom: 20px;
                        opacity: 0.5;
                    }
                    @media (max-width: 768px) {
                        .container { 
                            margin: 10px; 
                            padding: 20px; 
                        }
                        .page-header { 
                            padding: 20px; 
                        }
                        .seance .row > div { 
                            margin-bottom: 10px; 
                        }
                        .horaire {
                            font-size: 1rem;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="page-header">
                        <h1 class="display-4 mb-3">
                            <i class="fas fa-calendar-alt me-3"></i>
                            Emploi du Temps
                        </h1>
                        <h2 class="mb-0">Classe <xsl:value-of select="emploi/@classe"/></h2>
                        <p class="mb-0 mt-2 opacity-75">
                            <i class="fas fa-magic me-2"></i>
                            Généré par transformation XSLT
                        </p>
                    </div>
                    
                    <div class="stats">
                        <h3><xsl:value-of select="count(emploi/seance)"/></h3>
                        <p class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Séances programmées cette semaine
                        </p>
                    </div>
                    
                    <xsl:choose>
                        <xsl:when test="count(emploi/seance) > 0">
                            <!-- Grouper les séances par jour -->
                            <xsl:for-each select="emploi/seance[not(@jour = preceding-sibling::seance/@jour)]">
                                <xsl:variable name="jour" select="@jour"/>
                                <div class="jour-section">
                                    <div class="jour-header">
                                        <span>
                                            <i class="fas fa-calendar-day me-2"></i>
                                            <xsl:value-of select="@jour"/>
                                        </span>
                                        <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                            <xsl:value-of select="count(//seance[@jour = $jour])"/> séance(s)
                                        </span>
                                    </div>
                                    
                                    <!-- Afficher toutes les séances de ce jour -->
                                    <xsl:for-each select="//seance[@jour = $jour]">
                                        <xsl:sort select="@debut"/>
                                        <div class="seance">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <div class="horaire">
                                                        <i class="fas fa-clock"></i>
                                                        <xsl:value-of select="@debut"/> - <xsl:value-of select="@fin"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="module-badge">
                                                        <i class="fas fa-book"></i>
                                                        <xsl:value-of select="@module"/>
                                                    </span>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="prof-info">
                                                        <i class="fas fa-chalkboard-teacher"></i>
                                                        <xsl:value-of select="@prof"/>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="salle-info">
                                                        <i class="fas fa-door-open"></i>
                                                        <xsl:value-of select="@salle"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </xsl:for-each>
                                </div>
                            </xsl:for-each>
                        </xsl:when>
                        <xsl:otherwise>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times"></i>
                                <h4>Aucune séance programmée</h4>
                                <p>Il n'y a actuellement aucune séance dans l'emploi du temps de cette classe.</p>
                            </div>
                        </xsl:otherwise>
                    </xsl:choose>
                    
                    <div class="text-center mt-5">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Document généré automatiquement via transformation XSLT
                        </small>
                    </div>
                </div>
                
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>