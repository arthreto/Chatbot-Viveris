<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'classes/User.php';

// Récupérer le code d'autorisation
if (!isset($_GET['code'])) {
    header('Location: index.php');
    exit;
}

$code = $_GET['code'];

// Échanger le code contre un token
$tokenUrl = 'https://oauth2.googleapis.com/token';
$tokenData = [
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
];

$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenData));
$response = curl_exec($ch);
curl_close($ch);

$token = json_decode($response, true);

if (!isset($token['access_token'])) {
    header('Location: index.php?error=auth_failed');
    exit;
}

// Récupérer les informations utilisateur
$userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $token['access_token'];
$userInfo = json_decode(file_get_contents($userInfoUrl), true);

if (!$userInfo || !isset($userInfo['id'])) {
    header('Location: index.php?error=user_info_failed');
    exit;
}

// Créer ou mettre à jour l'utilisateur
$database = new Database();
$db = $database->getConnection();
$userObj = new User($db);

$user = $userObj->createOrUpdate(
    $userInfo['id'],
    $userInfo['email'],
    $userInfo['name'],
    $userInfo['picture'] ?? ''
);

if ($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    header('Location: index.php');
} else {
    header('Location: index.php?error=db_error');
}
exit;
?>

