<?php 
require 'config.php'; 
$user = requireRole($pdo, 'ROLE_USER');
$id = $_GET['id'] ?? null;
if (!$id || !requireOwner($pdo, $id)) { http_response_code(403); include '403.php'; exit; }

$stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
$stmt->execute([$id]);
$contact = $stmt->fetch();

if (!$contact) {
    header('Location: index.php');
    exit;
}

$tagsStmt = $pdo->prepare("SELECT label FROM tags WHERE id IN (SELECT tag_id FROM contact_tag WHERE contact_id = ?)");
$tagsStmt->execute([$id]);
$currentTags = $tagsStmt->fetchAll(PDO::FETCH_COLUMN);

$error = '';

if ($_POST) {
    $photoPath = $contact['photo_path'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, $allowedTypes) || $_FILES['photo']['size'] > 2*1024*1024) {
            $error = 'Photo invalide (JPG/PNG/WEBP, max 2MB).';
        } else {
            if ($photoPath && file_exists($photoPath)) unlink($photoPath);
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photoPath = 'uploads/photos/' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
        }
    }

    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE contacts SET name=?, email=?, phone=?, city=?, company=?, notes=?, photo_path=? WHERE id=?");
        $stmt->execute([$_POST['name'], $_POST['email'], $_POST['phone'], $_POST['city'], $_POST['company'], $_POST['notes'], $photoPath, $id]);
        
        // Supprimer anciens tags
        $pdo->prepare("DELETE FROM contact_tag WHERE contact_id = ?")->execute([$id]);
        
        // Ajouter nouveaux tags
        if (!empty($_POST['tags'])) {
            $tagStmt = $pdo->prepare("SELECT id FROM tags WHERE label = ?");
            $insertTag = $pdo->prepare("INSERT IGNORE INTO tags (label) VALUES (?)");
            $linkStmt = $pdo->prepare("INSERT INTO contact_tag (contact_id, tag_id) VALUES (?, ?)");
            foreach (explode(',', $_POST['tags']) as $t) {
                $t = trim($t);
                $insertTag->execute([$t]);
                $tagStmt->execute([$t]);
                $tagId = $tagStmt->fetchColumn();
                if ($tagId) $linkStmt->execute([$id, $tagId]);
            }
        }

        header('Location: show.php?id=' . $id);
        exit;
    }
}

$allTagsStmt = $pdo->query("SELECT label FROM tags ORDER BY label");
$allTags = $allTagsStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer Contact - HIBOS CRM</title>
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
            <div class="page-header">
                <h1 class="page-title">✏️ Éditer le Contact</h1>
                <p class="page-subtitle">Modifiez les informations du contact</p>
            </div>

            <div class="form-container">
                <?php if(isset($error) && !empty($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="form">
                    <!-- Ligne 1: Nom et Email -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">👤 Nom Complet *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($contact['name']); ?>"
                                   placeholder="Entrez le nom complet du contact">
                        </div>
                        <div class="form-group">
                            <label for="email">📧 Email</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($contact['email']); ?>"
                                   placeholder="exemple@email.com">
                        </div>
                    </div>

                    <!-- Ligne 2: Téléphone et Ville -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">📱 Téléphone</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($contact['phone']); ?>"
                                   placeholder="+33 6 12 34 56 78">
                        </div>
                        <div class="form-group">
                            <label for="city">🏙️ Ville</label>
                            <input type="text" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($contact['city']); ?>"
                                   placeholder="Ville du contact">
                        </div>
                    </div>

                    <!-- Entreprise -->
                    <div class="form-group">
                        <label for="company">🏢 Entreprise</label>
                        <input type="text" id="company" name="company" 
                               value="<?php echo htmlspecialchars($contact['company']); ?>"
                               placeholder="Nom de l'entreprise">
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes">📝 Notes</label>
                        <textarea id="notes" name="notes" 
                                  placeholder="Ajoutez des notes ou commentaires sur ce contact..."><?php echo htmlspecialchars($contact['notes']); ?></textarea>
                    </div>

                    <!-- Photo -->
                    <div class="form-group">
                        <label for="photo">📸 Photo du Contact</label>
                        <?php if($contact['photo_path']): ?>
                            <div style="margin-bottom: 15px; text-align: center;">
                                <img src="<?php echo htmlspecialchars($contact['photo_path']); ?>" 
                                     style="max-width: 150px; border-radius: 8px; box-shadow: var(--shadow);">
                                <p style="margin-top: 10px; color: #6b7280; font-size: 13px;">Photo actuelle</p>
                            </div>
                        <?php endif; ?>
                        <div class="upload-area" onclick="document.getElementById('photo').click()">
                            <div class="upload-icon">📸</div>
                            <div class="upload-text">Cliquez pour changer la photo</div>
                            <div class="upload-hint">Format: JPG, PNG, WEBP (Max 2MB)</div>
                        </div>
                        <input type="file" id="photo" name="photo" 
                               accept="image/jpeg,image/png,image/webp">
                    </div>

                    <!-- Tags -->
                    <div class="form-group">
                        <label for="tags">🏷️ Tags (séparés par virgule)</label>
                        <input type="text" id="tags" name="tags" 
                               placeholder="ex: VIP, Prospect, Client"
                               value="<?php echo htmlspecialchars(implode(', ', $currentTags)); ?>">
                        <div class="upload-hint" style="margin-top: 8px;">
                            Tags disponibles: <?php echo htmlspecialchars(implode(', ', $allTags)); ?>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            ✓ Mettre à Jour
                        </button>
                        <a href="show.php?id=<?php echo $id; ?>" class="btn btn-secondary" style="flex: 1;">
                            ← Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.getElementById('photo');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#2563eb';
            uploadArea.style.background = '#eff6ff';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.borderColor = '#e5e7eb';
            uploadArea.style.background = '#f9fafb';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.borderColor = '#e5e7eb';
            uploadArea.style.background = '#f9fafb';
            
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
            }
        });
    </script>
</body>
</html>
