<?php
// Test de connexion avec l'utilisateur dédié
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=maternite_db;charset=utf8mb4',
        'maternite_user',
        'Maternite@2024#',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    echo "✅ Connexion à la base de données réussie !\n\n";
    
    // Vérifier les utilisateurs
    $users = $pdo->query("SELECT id, username, role FROM utilisateurs")->fetchAll();
    echo "👤 Utilisateurs :\n";
    foreach ($users as $user) {
        echo "   - ID: {$user['id']}, Username: {$user['username']}, Role: {$user['role']}\n";
    }
    
    // Vérifier les données
    $count = $pdo->query("SELECT COUNT(*) as total FROM patients")->fetch();
    echo "\n👶 Patients : {$count['total']}\n";
    
    $count = $pdo->query("SELECT COUNT(*) as total FROM dossiers")->fetch();
    echo "📄 Dossiers : {$count['total']}\n";
    
    echo "\n🎯 Tous les tests sont passés avec succès !\n";
    echo "🔑 Login: admin / admin123\n";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
}
?>
