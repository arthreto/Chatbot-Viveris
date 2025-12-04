<?php
/**
 * Script de vérification de l'environnement
 * Accédez à ce fichier via votre navigateur pour vérifier la configuration
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de l'environnement - Chatbot</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            min-height: 100vh;
            padding: 2rem;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #1e293b;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }
        h1 {
            margin-bottom: 2rem;
            color: #6366f1;
        }
        .check-item {
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 8px;
            background: #334155;
        }
        .check-item.success {
            border-left: 4px solid #10b981;
        }
        .check-item.error {
            border-left: 4px solid #ef4444;
        }
        .check-item.warning {
            border-left: 4px solid #f59e0b;
        }
        .status {
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .success .status { color: #10b981; }
        .error .status { color: #ef4444; }
        .warning .status { color: #f59e0b; }
        code {
            background: #0f172a;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #f59e0b;
        }
        ul {
            margin-left: 1.5rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Vérification de l'environnement</h1>
        
        <?php
        $allOk = true;
        
        // Vérifier PHP
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '7.4.0', '>=');
        $allOk = $allOk && $phpOk;
        ?>
        <div class="check-item <?php echo $phpOk ? 'success' : 'error'; ?>">
            <div class="status"><?php echo $phpOk ? '✅' : '❌'; ?> Version PHP</div>
            <div>Version actuelle : <code><?php echo $phpVersion; ?></code></div>
            <?php if (!$phpOk): ?>
                <div style="margin-top: 0.5rem;">⚠️ PHP 7.4 ou supérieur est requis</div>
            <?php endif; ?>
        </div>

        <?php
        // Vérifier PDO SQLite
        $sqliteOk = extension_loaded('pdo_sqlite');
        $allOk = $allOk && $sqliteOk;
        ?>
        <div class="check-item <?php echo $sqliteOk ? 'success' : 'error'; ?>">
            <div class="status"><?php echo $sqliteOk ? '✅' : '❌'; ?> Extension PDO SQLite</div>
            <?php if ($sqliteOk): ?>
                <div>L'extension est activée et prête à être utilisée.</div>
            <?php else: ?>
                <div>
                    <strong>L'extension n'est pas activée.</strong>
                    <ul style="margin-top: 0.5rem;">
                        <li>Ubuntu/Debian : <code>sudo apt-get install php<?php echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION; ?>-sqlite3</code></li>
                        <li>CentOS/RHEL : <code>sudo yum install php-pdo php-sqlite3</code></li>
                        <li>Puis redémarrer PHP-FPM : <code>sudo systemctl restart php<?php echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION; ?>-fpm</code></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Vérifier les permissions du dossier data
        $dataDir = __DIR__ . '/data';
        $dataDirExists = file_exists($dataDir);
        $dataDirWritable = $dataDirExists && is_writable($dataDir);
        
        if (!$dataDirExists) {
            $canCreate = is_writable(dirname($dataDir));
        } else {
            $canCreate = true;
        }
        
        $dataOk = $dataDirWritable || $canCreate;
        $allOk = $allOk && $dataOk;
        ?>
        <div class="check-item <?php echo $dataOk ? 'success' : 'error'; ?>">
            <div class="status"><?php echo $dataOk ? '✅' : '❌'; ?> Dossier data</div>
            <?php if ($dataDirExists): ?>
                <div>Le dossier existe : <code><?php echo $dataDir; ?></code></div>
                <div>Permissions : <?php echo $dataDirWritable ? '✅ Écriture autorisée' : '❌ Pas d\'écriture'; ?></div>
            <?php else: ?>
                <div>Le dossier n'existe pas mais peut être créé : <?php echo $canCreate ? '✅' : '❌'; ?></div>
            <?php endif; ?>
            <?php if (!$dataOk): ?>
                <div style="margin-top: 0.5rem;">
                    <strong>Solution :</strong> Donnez les permissions d'écriture : <code>chmod 755 data</code>
                </div>
            <?php endif; ?>
        </div>

        <?php
        // Vérifier cURL (pour OpenAI)
        $curlOk = extension_loaded('curl');
        ?>
        <div class="check-item <?php echo $curlOk ? 'success' : 'warning'; ?>">
            <div class="status"><?php echo $curlOk ? '✅' : '⚠️'; ?> Extension cURL</div>
            <?php if ($curlOk): ?>
                <div>cURL est activé (nécessaire pour l'API OpenAI).</div>
            <?php else: ?>
                <div>cURL n'est pas activé. L'API OpenAI ne fonctionnera pas.</div>
            <?php endif; ?>
        </div>

        <?php
        // Test de connexion SQLite
        if ($sqliteOk && $dataOk) {
            try {
                $testDb = __DIR__ . '/data/test.db';
                $pdo = new PDO("sqlite:" . $testDb);
                $pdo->exec("CREATE TABLE IF NOT EXISTS test (id INTEGER)");
                $pdo->exec("DROP TABLE test");
                unlink($testDb);
                $testOk = true;
            } catch (Exception $e) {
                $testOk = false;
                $testError = $e->getMessage();
            }
        } else {
            $testOk = false;
            $testError = "Prérequis non remplis";
        }
        ?>
        <div class="check-item <?php echo $testOk ? 'success' : 'error'; ?>">
            <div class="status"><?php echo $testOk ? '✅' : '❌'; ?> Test de connexion SQLite</div>
            <?php if ($testOk): ?>
                <div>La connexion SQLite fonctionne correctement !</div>
            <?php else: ?>
                <div>Erreur : <?php echo htmlspecialchars($testError ?? 'Inconnue'); ?></div>
            <?php endif; ?>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: <?php echo $allOk ? '#10b981' : '#ef4444'; ?>; border-radius: 8px; text-align: center;">
            <strong style="font-size: 1.2rem;">
                <?php if ($allOk): ?>
                    ✅ Tous les prérequis sont remplis ! Le chatbot devrait fonctionner.
                <?php else: ?>
                    ❌ Certains prérequis ne sont pas remplis. Veuillez corriger les erreurs ci-dessus.
                <?php endif; ?>
            </strong>
        </div>
    </div>
</body>
</html>

