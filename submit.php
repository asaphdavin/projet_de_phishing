<?php
// submit.php
// Enregistre email, hash du mot de passe, timestamp et IP dans un fichier CSV.
// DEFAUT: le fichier de stockage est ../data/credentials.csv (hors webroot si possible).
$storageFile = __DIR__ . '/../data/credentials.csv';

// Fonction utilitaire pour répondre puis sortir
function respond_and_exit($msg, $redirect = 'login.html') {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!doctype html><html><head><meta charset='utf-8'><meta http-equiv='refresh' content='2;url={$redirect}' /></head><body>";
    echo "<p>" . htmlspecialchars($msg, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . "</p>";
    echo "<p>Tu seras redirigé...</p></body></html>";
    exit;
}

// Récupération et validation
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

if (!$email || $password === '') {
    respond_and_exit('Données invalides. Veuillez réessayer.');
}

// Hachage du mot de passe (sécurisé)
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Détails additionnels utiles pour un test : IP et user agent
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
$timestamp = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d H:i:s'); // UTC

// Prépare la ligne CSV (échapper les champs)
$line = sprintf("\"%s\",\"%s\",\"%s\",\"%s\",\"%s\"\n",
    str_replace('"', '""', $timestamp),
    str_replace('"', '""', $email),
    str_replace('"', '""', $password_hash),
    str_replace('"', '""', $ip),
    str_replace('"', '""', $ua)
);

// Créer le dossier si nécessaire
$dir = dirname($storageFile);
if (!is_dir($dir)) {
    mkdir($dir, 0700, true);
}

// Écrire en append. Vérifier erreurs
if (file_put_contents($storageFile, $line, FILE_APPEND | LOCK_EX) === false) {
    respond_and_exit('Erreur serveur : impossible d\'écrire les données. Contacte l\'administrateur.');
}

// Rediriger vers une page de remerciement / succès pour l'utilisateur
respond_and_exit('Merci — connexion enregistrée.', 'thankyou.html');
?>
