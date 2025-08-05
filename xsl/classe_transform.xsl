<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8" doctype-public="-//W3C//DTD HTML 4.01//EN"/>
    
    <xsl:template match="/">
        <html lang="fr">
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
                <title>Classe <xsl:value-of select="classe/@filiere"/><xsl:value-of select="classe/@niveau"/> - Étudiants et Modules</title>
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
                    .section-card {
                        background: white;
                        border-radius: 15px;
                        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
                        margin-bottom: 30px;
                        overflow: hidden;
                    }
                    .section-header {
                        padding: 20px;
                        font-weight: bold;
                        font-size: 1.2rem;
                        display: flex;
                        align-items: center;
                        gap: 10px;
                    }
                    .etudiants-header {
                        background: linear-gradient(45deg, #28a745, #1e7e34);
                        color: white;
                    }
                    .modules-header {
                        background: linear-gradient(45deg, #17a2b8, #138496);
                        color: white;
                    }
                    .table {
                        margin-bottom: 0;
                    }
                    .table th {
                        background: #f8f9fa;
                        border-top: none;
                        font-weight: 600;
                        color: #495057;
                    }
                    .table-striped tbody tr:nth-of-type(odd) {
                        background-color: rgba(0, 123, 255, 0.05);
                    }
                    .badge-custom {
                        padding: 8px 12px;
                        border-radius: 20px;
                        font-size: 0.9rem;
                        font-weight: 500;
                    }
                    .stats-row {
                        background: #f8f9fa;
                        padding: 20px;
                        border-radius: 15px;
                        margin-bottom: 30px;
                        text-align: center;
                    }
                    .stat-card {
                        background: linear-gradient(45deg, #07c7e4, #048eea);
                        padding: 20px;
                        border-radius: 10px;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                        margin: 10px;
                    }
                    .stat-number {
                        font-size: 2rem;
                        font-weight: bold;
                        color: #0353c4ff;
                        display: block;
                    }
                    .stat-label {
                        font-size: 0.9rem;
                        color: #1a1d21ff;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    .empty-state {
                        text-align: center;
                        padding: 40px;
                        color: #6c757d;
                        font-style: italic;
                    }
                    @media (max-width: 768px) {
                        .container { 
                            margin: 10px; 
                            padding: 20px; 
                        }
                        .page-header { 
                            padding: 20px; 
                        }
                        .stat-card {
                            margin: 5px 0;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="page-header">
                        <h1 class="display-4 mb-3">
                            <i class="fas fa-graduation-cap me-3"></i>
                            Classe <xsl:value-of select="classe/@filiere"/><xsl:value-of select="classe/@niveau"/>
                        </h1>
                        <p class="mb-0">
                            <i class="fas fa-school me-2"></i>
                            Filière <xsl:value-of select="classe/@filiere"/> - Niveau <xsl:value-of select="classe/@niveau"/>
                        </p>
                    </div>
                    
                    <!-- Statistiques -->
                    <xsl:if test="classe/statistiques">
                        <div class="stats-row">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <span class="stat-number">
                                            <xsl:value-of select="classe/statistiques/nbEtudiants"/>
                                        </span>
                                        <span class="stat-label">Étudiants</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <span class="stat-number">
                                            <xsl:value-of select="classe/statistiques/nbModules"/>
                                        </span>
                                        <span class="stat-label">Modules</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="stat-card">
                                        <span class="stat-number">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <span class="stat-label">
                                            Généré le <xsl:value-of select="substring(classe/statistiques/dateGeneration, 1, 10)"/>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </xsl:if>
                    
                    <div class="row">
                        <!-- Section Étudiants -->
                        <div class="col-lg-6">
                            <div class="section-card">
                                <div class="section-header etudiants-header">
                                    <i class="fas fa-users"></i>
                                    Liste des Étudiants
                                    <span class="badge bg-light text-dark ms-auto">
                                        <xsl:value-of select="count(classe/etudiants/etudiant)"/> étudiant(s)
                                    </span>
                                </div>
                                <div class="table-responsive">
                                    <xsl:choose>
                                        <xsl:when test="count(classe/etudiants/etudiant) > 0">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><i class="fas fa-id-card me-2"></i>N° Inscription</th>
                                                        <th><i class="fas fa-user me-2"></i>Nom</th>
                                                        <th><i class="fas fa-user me-2"></i>Prénoms</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <xsl:for-each select="classe/etudiants/etudiant">
                                                        <xsl:sort select="@nom"/>
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-custom bg-primary">
                                                                    <xsl:value-of select="@numInscription"/>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <strong><xsl:value-of select="@nom"/></strong>
                                                            </td>
                                                            <td>
                                                                <xsl:value-of select="@prenom"/>
                                                            </td>
                                                        </tr>
                                                    </xsl:for-each>
                                                </tbody>
                                            </table>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <div class="empty-state">
                                                <i class="fas fa-user-slash fa-3x mb-3 opacity-50"></i>
                                                <p>Aucun étudiant inscrit dans cette classe</p>
                                            </div>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Section Modules -->
                        <div class="col-lg-6">
                            <div class="section-card">
                                <div class="section-header modules-header">
                                    <i class="fas fa-book"></i>
                                    Modules Enseignés
                                    <span class="badge bg-light text-dark ms-auto">
                                        <xsl:value-of select="count(classe/modules/module)"/> module(s)
                                    </span>
                                </div>
                                <div class="table-responsive">
                                    <xsl:choose>
                                        <xsl:when test="count(classe/modules/module) > 0">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th><i class="fas fa-hashtag me-2"></i>ID Module</th>
                                                        <th><i class="fas fa-book-open me-2"></i>Nom du Module</th>
                                                        <th><i class="fas fa-info-circle me-2"></i>Description</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <xsl:for-each select="classe/modules/module">
                                                        <xsl:sort select="@nomModule"/>
                                                        <tr>
                                                            <td>
                                                                <span class="badge badge-custom bg-info">
                                                                    <xsl:value-of select="@idModule"/>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <strong><xsl:value-of select="@nomModule"/></strong>
                                                            </td>
                                                            <td>
                                                                <xsl:choose>
                                                                    <xsl:when test="@description">
                                                                        <xsl:value-of select="@description"/>
                                                                    </xsl:when>
                                                                    <xsl:otherwise>
                                                                        <em class="text-muted">Pas de description</em>
                                                                    </xsl:otherwise>
                                                                </xsl:choose>
                                                            </td>
                                                        </tr>
                                                    </xsl:for-each>
                                                </tbody>
                                            </table>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <div class="empty-state">
                                                <i class="fas fa-book-dead fa-3x mb-3 opacity-50"></i>
                                                <p>Aucun module assigné à cette classe</p>
                                            </div>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-magic me-1"></i>
                            Document généré automatiquement via transformation XSLT
                        </small>
                    </div>
                </div>
                
                <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>