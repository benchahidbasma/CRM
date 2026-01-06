<?php 
require 'config.php'; 
$user = requireRole($pdo, 'ROLE_USER');
$id = $_GET['id'];
if (!requireOwner($pdo, $id)) { include '403.php'; exit; }

if ($_POST && $_POST['confirm'] === 'yes') {
    // Supprimer photo si existe
    $stmt = $pdo->prepare("SELECT photo_path FROM contacts WHERE id=?");
    $stmt->execute([$id]);
    if ($path = $stmt->fetchColumn() && file_exists($path)) unlink($path);
    
    $pdo->prepare("DELETE FROM contacts WHERE id=?")->execute([$id]);
    header('Location: index.php');
    exit;
}
?>
<h1>Confirmer suppression</h1>
<p>Supprimer <?php echo htmlspecialchars($_GET['name'] ?? 'ce contact'); ?> ?</p>
<form method="POST">
    <button name="confirm" value="yes">Oui</button>
    <button name="confirm" value="no">Non</button>
</form>
