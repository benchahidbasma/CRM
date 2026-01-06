<?php 
require 'config.php'; 
$user = requireRole($pdo, ['ROLE_USER', 'ROLE_ADMIN']);
$id = $_GET['id'];
if (!($user['roles'][0]==='ROLE_ADMIN' || requireOwner($pdo, $id))) { include '403.php'; exit; }

$stmt = $pdo->prepare("SELECT * FROM contacts WHERE id=?");
$stmt->execute([$id]);
$contact = $stmt->fetch();

if ($_POST) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'localhost';
        $mail->SMTPAuth = false;
        $mail->Port = 1025; // MailHog
        $mail->setFrom('no-reply@crm.com');
        $mail->addAddress($contact['email']);
        $mail->Subject = $_POST['subject'];
        $mail->Body = $_POST['message'];
        $mail->send();
        echo '<p>Email envoyé (vérifiez MailHog)!</p>';
    } catch (Exception $e) {
        echo '<p>Erreur: ' . $mail->ErrorInfo . '</p>';
    }
}
?>
<h1>Envoyer Email à <?php echo htmlspecialchars($contact['name']); ?></h1>
<form method="POST">
    Sujet: <input name="subject" required><br>
    Message: <textarea name="message" required></textarea><br>
    <button>Envoyer</button>
</form>
