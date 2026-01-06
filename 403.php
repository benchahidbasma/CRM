<?php
$logFile = 'logs/403.log';
$logMessage = date('Y-m-d H:i:s') . " - 403 Access - IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . " - URL: " . ($_SERVER['REQUEST_URI'] ?? 'unknown') . " - User-Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . "\n";
file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Accès Interdit - HIBOS CRM</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #acbbdbff 0%, #bee095ff 100%);
            padding: 20px;
        }

        .error-content {
            background: white;
            border-radius: 12px;
            padding: 60px 40px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .error-icon {
            font-size: 100px;
            margin-bottom: 20px;
        }

        .error-code {
            font-size: 48px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 10px;
        }

        .error-title {
            font-size: 24px;
            color: #111827;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .error-message {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-info {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            color: #991b1b;
            font-size: 14px;
        }

        @media (max-width: 480px) {
            .error-content {
                padding: 40px 20px;
            }

            .error-code {
                font-size: 36px;
            }

            .error-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-content">
            <div class="error-icon">🔒</div>
            <div class="error-code">403</div>
            <h1 class="error-title">Accès Interdit</h1>
            <p class="error-message">
                Vous n'avez pas les droits nécessaires pour accéder à cette ressource.<br>
                Seuls les utilisateurs autorisés peuvent y accéder.
            </p>
            <div class="error-info">
                ⚠️ Si vous pensez qu'il y a une erreur, veuillez contacter l'administrateur.
            </div>
            <a href="index.php" class="btn btn-primary">← Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
