<?php 
require 'config.php'; 
$user = requireRole($pdo, ['ROLE_USER', 'ROLE_ADMIN']);

$q = $_GET['q'] ?? '';
$city = $_GET['city'] ?? '';
$tag = $_GET['tag'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$dir = $_GET['dir'] ?? 'ASC';

// Cookie dernier filtre
if ($_GET && array_filter($_GET, fn($v)=>$v!=='')) {
    setcookie('last_filter', json_encode($_GET), time()+3600*24*30, '/');
}

// Appliquer cookie si pas de GET
if (empty($_GET['q']) && empty($_GET['city']) && empty($_GET['tag']) && empty($_GET['sort'])) {
    if (isset($_COOKIE['last_filter'])) {
        $last = json_decode($_COOKIE['last_filter'], true);
        $_GET = array_merge($_GET, $last);
    }
}

$where = [];
$params = [];
if ($q) { $where[] = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR notes LIKE ?)"; $params = array_fill(0,4, "%$q%"); }
if ($city) { $where[] = "city LIKE ?"; $params[] = "%$city%"; }
if ($tag) { $where[] = "id IN (SELECT contact_id FROM contact_tag ct JOIN tags t ON ct.tag_id=t.id WHERE t.label LIKE ?)"; $params[] = "%$tag%"; }

$ownerFilter = $user['roles'][0] === 'ROLE_ADMIN' ? '' : ' AND owner_id = ' . $user['id'];

$sql = "SELECT c.*, GROUP_CONCAT(t.label SEPARATOR ', ') as tags 
        FROM contacts c 
        LEFT JOIN contact_tag ct ON c.id=ct.contact_id 
        LEFT JOIN tags t ON ct.tag_id=t.id 
        WHERE " . (empty($where) ? '1=1' : implode(' AND ', $where)) . "$ownerFilter 
        GROUP BY c.id 
        ORDER BY $sort $dir";
if (!empty($where) || $tag) $sql = "SELECT c.*, GROUP_CONCAT(t.label) as tags FROM contacts c LEFT JOIN ... " . implode(' AND ', $where) . " GROUP BY c.id ORDER BY ..."; // Simplifié, utiliser bind pour tag

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$contacts = $stmt->fetchAll();

$tagsStmt = $pdo->query("SELECT DISTINCT label FROM tags ORDER BY label");
$tags = $tagsStmt->fetchAll(PDO::FETCH_COLUMN);

// Debug helper (temporary): visit index.php?debug=1 to display session and roles
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
    <title>Contacts - HIBOS CRM</title>
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
                    <?php if ($user['roles'][0]==='ROLE_ADMIN'): ?>
                        <li><a href="#">📊 Dashboard</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php" class="btn-logout">🚪 Déconnexion</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?php echo $debugHtml; ?>
            <div class="page-header flex-between" style="margin-bottom: 30px;">
                <div>
                    <h1 class="page-title">📋 Contacts</h1>
                    <p class="page-subtitle"><?php echo count($contacts); ?> contact(s) trouvé(s)</p>
                </div>
                <a href="create.php" class="btn btn-primary">➕ Ajouter Contact</a>
            </div>

            <!-- FILTRES -->
            <div class="filter-section">
                <form method="GET" class="filter-form">
                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="q">🔍 Rechercher</label>
                            <input type="text" id="q" name="q" 
                                   value="<?php echo htmlspecialchars($q); ?>" 
                                   placeholder="Nom, email, téléphone...">
                        </div>
                        <div class="filter-group">
                            <label for="city">🏙️ Ville</label>
                            <input type="text" id="city" name="city" 
                                   value="<?php echo htmlspecialchars($city); ?>" 
                                   placeholder="Filtrer par ville">
                        </div>
                        <div class="filter-group">
                            <label for="tag">🏷️ Tag</label>
                            <select id="tag" name="tag">
                                <option value="">-- Tous les tags --</option>
                                <?php foreach($tags as $t): ?>
                                    <option value="<?php echo htmlspecialchars($t); ?>" 
                                            <?php echo $tag === $t ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($t); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <div class="filter-group">
                            <label for="sort">⬇️ Trier par</label>
                            <select id="sort" name="sort">
                                <option value="name" <?php echo $sort=='name'?'selected':'';?>>Nom (A-Z)</option>
                                <option value="city" <?php echo $sort=='city'?'selected':'';?>>Ville</option>
                                <option value="created_at" <?php echo $sort=='created_at'?'selected':'';?>>Date d'ajout</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="dir">📊 Ordre</label>
                            <select id="dir" name="dir">
                                <option value="ASC" <?php echo $dir=='ASC'?'selected':'';?>>Croissant (↑)</option>
                                <option value="DESC" <?php echo $dir=='DESC'?'selected':'';?>>Décroissant (↓)</option>
                            </select>
                        </div>
                        <div class="filter-group" style="justify-content: flex-end;">
                            <button type="submit" class="btn btn-primary">🔍 Rechercher</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TABLEAU DE CONTACTS -->
            <?php if (count($contacts) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>👤 Nom</th>
                                <th>📧 Email</th>
                                <th>📱 Téléphone</th>
                                <th>🏙️ Ville</th>
                                <th>🏢 Entreprise</th>
                                <th>🏷️ Tags</th>
                                <th style="text-align: center;">⚙️ Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($contacts as $c): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($c['name']); ?></strong>
                                        <?php if($c['photo_path']): ?>
                                            <br><img src="<?php echo htmlspecialchars($c['photo_path']); ?>" 
                                                     style="width: 30px; height: 30px; border-radius: 50%; margin-top: 5px;">
                                        <?php endif; ?>
                                    </td>
                                    <td><a href="mailto:<?php echo htmlspecialchars($c['email']); ?>" 
                                           style="color: #2563eb; text-decoration: none;">
                                           <?php echo htmlspecialchars($c['email']); ?>
                                        </a></td>
                                    <td><a href="tel:<?php echo htmlspecialchars($c['phone']); ?>" 
                                           style="color: #2563eb; text-decoration: none;">
                                           <?php echo htmlspecialchars($c['phone']); ?>
                                        </a></td>
                                    <td><?php echo htmlspecialchars($c['city'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($c['company'] ?? '-'); ?></td>
                                    <td>
                                        <?php if($c['tags']): ?>
                                            <?php foreach(array_filter(explode(',', $c['tags'])) as $tag): ?>
                                                <span class="card-badge primary" style="margin-right: 4px;">
                                                    <?php echo htmlspecialchars(trim($tag)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <div class="table-actions" style="justify-content: center;">
                                            <a href="show.php?id=<?php echo $c['id']; ?>" 
                                               class="action-btn view">👁️ Voir</a>
                                            <a href="edit.php?id=<?php echo $c['id']; ?>" 
                                               class="action-btn edit">✏️ Éditer</a>
                                            <a href="email.php?id=<?php echo $c['id']; ?>" 
                                               class="action-btn" style="background: #fef3c7; color: #d97706;">📧 Email</a>
                                            <a href="delete.php?id=<?php echo $c['id']; ?>" 
                                               class="action-btn delete" 
                                               onclick="return confirm('Confirmer la suppression?')">🗑️ Supprimer</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 60px 20px;">
                    <div style="font-size: 80px; margin-bottom: 20px;">📭</div>
                    <h2 style="color: #111827; margin-bottom: 10px;">Aucun contact trouvé</h2>
                    <p class="text-muted" style="margin-bottom: 30px;">Créez votre premier contact pour commencer</p>
                    <a href="create.php" class="btn btn-primary">➕ Ajouter Contact</a>
                </div>
            <?php endif; ?>

            <!-- TAGS DISPONIBLES -->
            <?php if (count($tags) > 0): ?>
                <div style="margin-top: 40px; padding: 30px; background: white; border-radius: 12px; box-shadow: var(--shadow);">
                    <h3 style="margin-bottom: 20px; color: #111827;">🏷️ Tags Disponibles</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach($tags as $t): ?>
                            <a href="?tag=<?php echo urlencode($t); ?>" 
                               class="card-badge primary" 
                               style="text-decoration: none; cursor: pointer; transition: all 0.3s;">
                                📌 <?php echo htmlspecialchars($t); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
