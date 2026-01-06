<?php 
require 'config.php'; 
$user = requireRole($pdo, ['ROLE_USER', 'ROLE_ADMIN']);
$id = $_GET['id'] ?? null;
if (!$id || !($user['roles'][0]==='ROLE_ADMIN' || requireOwner($pdo, $id))) { include '403.php'; exit; }

$stmt = $pdo->prepare("SELECT c.* FROM contacts c WHERE c.id=?");
$stmt->execute([$id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    header('Location: index.php');
    exit;
}

$tagsStmt = $pdo->prepare("SELECT t.label FROM tags t JOIN contact_tag ct ON t.id=ct.tag_id WHERE ct.contact_id=? ORDER BY t.label");
$tagsStmt->execute([$id]);
$tags = $tagsStmt->fetchAll(PDO::FETCH_COLUMN);
// Debug helper (temporary): visit show.php?id=...&debug=1 to display session and roles
$debugHtml = '';
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    $sessId = $_SESSION['user_id'] ?? null;
    $dbUser = getCurrentUser($pdo);
    $userRoles = $dbUser['roles'] ?? null;
    $debugHtml = '<div style="position:fixed;right:10px;bottom:10px;background:rgba(255,255,255,0.95);padding:12px;border:1px solid #e5e7eb;border-radius:8px;z-index:9999;font-size:13px;">'
               . '<strong>DEBUG</strong><br>'
               . 'session user_id: ' . htmlspecialchars((string)$sessId) . '<br>'
               . 'db user roles: ' . htmlspecialchars(is_scalar($userRoles) ? (string)$userRoles : json_encode($userRoles))
               . '</div>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($contact['name']); ?> - HIBOS CRM</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">HIBOS CRM</a>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">📋 Contacts</a></li>
                    <li><a href="create.php">➕ Ajouter</a></li>
                    <li><a href="logout.php" class="btn-logout">🚪 Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?php echo $debugHtml; ?>
            <a href="index.php" class="btn btn-secondary" style="margin-bottom: 20px;">← Retour aux Contacts</a>

            <div style="background: white; border-radius: 12px; overflow: hidden; box-shadow: var(--shadow-lg);">
                <!-- En-tête avec photo -->
                <div style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); padding: 40px 20px; text-align: center; color: white;">
                    <?php if($contact['photo_path']): ?>
                        <img src="<?php echo htmlspecialchars($contact['photo_path']); ?>" 
                             style="width: 120px; height: 120px; border-radius: 50%; border: 4px solid white; margin-bottom: 20px; object-fit: cover;">
                    <?php else: ?>
                        <div style="width: 120px; height: 120px; background: rgba(255,255,255,0.3); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 50px;">
                            👤
                        </div>
                    <?php endif; ?>
                    <h1 style="margin: 0; font-size: 32px;">👤 <?php echo htmlspecialchars($contact['name']); ?></h1>
                </div>

                <!-- Contenu -->
                <div style="padding: 40px;">
                    <!-- Grille d'informations -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-bottom: 40px;">
                        <!-- Email -->
                        <div style="padding: 20px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #059669;">
                            <div style="color: #6b7280; font-size: 13px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">📧 Email</div>
                            <a href="mailto:<?php echo htmlspecialchars($contact['email']); ?>" 
                               style="color: #059669; text-decoration: none; font-size: 16px; font-weight: 600;">
                                <?php echo htmlspecialchars($contact['email'] ?? '—'); ?>
                            </a>
                        </div>

                        <!-- Téléphone -->
                        <div style="padding: 20px; background: #fef3c7; border-radius: 8px; border-left: 4px solid #d97706;">
                            <div style="color: #6b7280; font-size: 13px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">📱 Téléphone</div>
                            <a href="tel:<?php echo htmlspecialchars($contact['phone']); ?>" 
                               style="color: #d97706; text-decoration: none; font-size: 16px; font-weight: 600;">
                                <?php echo htmlspecialchars($contact['phone'] ?? '—'); ?>
                            </a>
                        </div>

                        <!-- Ville -->
                        <div style="padding: 20px; background: #ede9fe; border-radius: 8px; border-left: 4px solid #7c3aed;">
                            <div style="color: #6b7280; font-size: 13px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">🏙️ Ville</div>
                            <div style="color: #7c3aed; font-size: 16px; font-weight: 600;">
                                <?php echo htmlspecialchars($contact['city'] ?? '—'); ?>
                            </div>
                        </div>

                        <!-- Entreprise -->
                        <div style="padding: 20px; background: #dbeafe; border-radius: 8px; border-left: 4px solid #2563eb; grid-column: 1 / -1;">
                            <div style="color: #6b7280; font-size: 13px; text-transform: uppercase; font-weight: 600; margin-bottom: 8px;">🏢 Entreprise</div>
                            <div style="color: #2563eb; font-size: 16px; font-weight: 600;">
                                <?php echo htmlspecialchars($contact['company'] ?? '—'); ?>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <?php if($contact['notes']): ?>
                        <div style="margin-bottom: 40px; padding: 20px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #6b7280;">
                            <h3 style="margin: 0 0 12px 0; color: #111827;">📝 Notes</h3>
                            <p style="margin: 0; color: #374151; line-height: 1.6; white-space: pre-wrap;">
                                <?php echo htmlspecialchars($contact['notes']); ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Tags -->
                    <?php if(count($tags) > 0): ?>
                        <div style="margin-bottom: 40px;">
                            <h3 style="margin: 0 0 15px 0; color: #111827;">🏷️ Tags</h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                <?php foreach($tags as $tag): ?>
                                    <span class="card-badge primary">
                                        📌 <?php echo htmlspecialchars($tag); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Informations de métadonnées -->
                    <div style="padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 13px;">
                        <p style="margin: 5px 0;">
                            <strong>ID:</strong> #<?php echo $contact['id']; ?>
                        </p>
                        <?php if($contact['created_at']): ?>
                            <p style="margin: 5px 0;">
                                <strong>Créé:</strong> <?php echo date('d/m/Y à H:i', strtotime($contact['created_at'])); ?>
                            </p>
                        <?php endif; ?>
                        <?php if($contact['updated_at']): ?>
                            <p style="margin: 5px 0;">
                                <strong>Modifié:</strong> <?php echo date('d/m/Y à H:i', strtotime($contact['updated_at'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="btn-group" style="margin-top: 40px;">
                        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-primary" style="flex: 1;">
                            ✏️ Éditer
                        </a>
                        <a href="email.php?id=<?php echo $id; ?>" class="btn btn-secondary" style="flex: 1;">
                            📧 Envoyer Email
                        </a>
                        <a href="delete.php?id=<?php echo $id; ?>" class="btn btn-danger" style="flex: 1;" 
                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contact?');">
                            🗑️ Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
