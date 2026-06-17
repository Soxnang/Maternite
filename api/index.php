<?php
/**
 * API REST - Système de Maternité
 * Version 2.0 - Optimisée
 */

// ============================================================
// CONFIGURATION
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// ============================================================
// HEADERS CORS
// ============================================================
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ============================================================
// AUTOLOADER
// ============================================================
require_once __DIR__ . '/config/database.php';

// Autoloader simple pour les classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// ============================================================
// FONCTIONS UTILES
// ============================================================

/**
 * Envoie une réponse JSON formatée
 */
function sendResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Envoie une réponse d'erreur
 */
function sendError($message, $status = 400, $details = null) {
    $response = [
        'success' => false,
        'error' => $message
    ];
    if ($details) {
        $response['details'] = $details;
    }
    sendResponse($response, $status);
}

/**
 * Envoie une réponse de succès
 */
function sendSuccess($data, $message = null, $status = 200) {
    $response = [
        'success' => true
    ];
    if ($message) {
        $response['message'] = $message;
    }
    if ($data !== null) {
        $response['data'] = $data;
    }
    sendResponse($response, $status);
}

/**
 * Vérifie l'authentification et retourne l'utilisateur
 */
function authenticate() {
    $headers = getallheaders();
    $auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
    
    if (empty($auth) || !str_starts_with($auth, 'Bearer ')) {
        sendError('Non authentifié', 401);
    }
    
    $token = substr($auth, 7);
    $userData = json_decode(base64_decode($token), true);
    
    if (!$userData || !isset($userData['id'])) {
        sendError('Token invalide', 401);
    }
    
    return $userData;
}

/**
 * Valide les données requises
 */
function validateRequired($data, $fields) {
    $missing = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        sendError('Champs requis manquants: ' . implode(', ', $missing), 400);
    }
}

// ============================================================
// ROUTING
// ============================================================

// Récupération de la route
$route = isset($_GET['route']) ? $_GET['route'] : '';

if (empty($route)) {
    $uri = $_SERVER['REQUEST_URI'];
    $base = dirname($_SERVER['SCRIPT_NAME']);
    if ($base != '/') {
        $route = substr($uri, strlen($base));
    } else {
        $route = $uri;
    }
    if (strpos($route, '?') !== false) {
        $route = substr($route, 0, strpos($route, '?'));
    }
    $route = ltrim($route, '/');
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

$uri_parts = explode('/', $route);
$resource = isset($uri_parts[0]) ? $uri_parts[0] : '';
$id = isset($uri_parts[1]) ? $uri_parts[1] : null;
$sub = isset($uri_parts[2]) ? $uri_parts[2] : null;

// ============================================================
// BASE DE DONNÉES
// ============================================================
try {
    $db = App\Config\Database::getConnection();
} catch (Exception $e) {
    sendError('Erreur de connexion à la base de données: ' . $e->getMessage(), 500);
}

// ============================================================
// ROUTES PUBLIQUES
// ============================================================

// Route racine - Documentation
if (empty($resource)) {
    sendSuccess([
        'name' => 'API Maternité Digital',
        'version' => '2.0',
        'endpoints' => [
            'auth' => [
                'POST /auth/login' => 'Authentification'
            ],
            'patients' => [
                'GET /patients' => 'Liste des patients',
                'POST /patients' => 'Créer un patient',
                'GET /patients/{id}' => 'Détail patient',
                'PUT /patients/{id}' => 'Modifier patient',
                'DELETE /patients/{id}' => 'Supprimer patient'
            ],
            'dossiers' => [
                'GET /dossiers' => 'Liste des dossiers',
                'POST /dossiers' => 'Créer un dossier',
                'GET /dossiers/{id}' => 'Détail dossier',
                'DELETE /dossiers/{id}' => 'Supprimer dossier',
                'GET /dossiers/search?q={term}' => 'Rechercher'
            ],
            'stats' => [
                'GET /stats/dashboard' => 'Statistiques'
            ],
            'test' => [
                'GET /test/db' => 'Test connexion'
            ]
        ]
    ], 200);
}

// Route de test
if ($resource === 'test' && $id === 'db') {
    try {
        $stmt = $db->query("SELECT 1");
        sendSuccess(['message' => 'Connexion à la base de données réussie']);
    } catch (Exception $e) {
        sendError('Erreur de connexion: ' . $e->getMessage(), 500);
    }
}

// Route d'authentification
if ($resource === 'auth') {
    if ($id === 'login' && $method === 'POST') {
        validateRequired($input, ['username', 'password']);
        
        $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE username = :username AND is_active = 1");
        $stmt->execute(['username' => $input['username']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($input['password'], $user['password_hash'])) {
            sendError('Identifiants invalides', 401);
        }
        
        // Mettre à jour la dernière connexion
        $stmt = $db->prepare("UPDATE utilisateurs SET last_login = NOW() WHERE id = :id");
        $stmt->execute(['id' => $user['id']]);
        
        // Générer un token
        $token = base64_encode(json_encode([
            'id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'exp' => time() + 86400 // 24h
        ]));
        
        sendSuccess([
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'nom_complet' => $user['nom_complet'],
                'email' => $user['email']
            ]
        ], 'Connexion réussie');
    }
    
    sendError('Route auth non trouvée', 404);
}

// ============================================================
// ROUTES PROTÉGÉES
// ============================================================

// Authentification requise pour toutes les routes suivantes
$currentUser = authenticate();

// ============================================================
// ROUTE : PATIENTS
// ============================================================
if ($resource === 'patients') {
    switch ($method) {
        case 'GET':
            if ($id && is_numeric($id)) {
                // Détail d'un patient
                $stmt = $db->prepare("SELECT * FROM patients WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $patient = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$patient) {
                    sendError('Patient non trouvé', 404);
                }
                
                sendSuccess($patient);
            } else {
                // Liste des patients
                $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                
                $stmt = $db->prepare("SELECT * FROM patients ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $total = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
                
                sendSuccess([
                    'items' => $patients,
                    'total' => (int)$total,
                    'limit' => $limit,
                    'offset' => $offset
                ]);
            }
            break;
            
        case 'POST':
            // Créer un patient
            validateRequired($input, ['prenom', 'nom']);
            
            $stmt = $db->prepare("INSERT INTO patients (prenom, nom, age, adresse, telephone) 
                                 VALUES (:prenom, :nom, :age, :adresse, :telephone)");
            $stmt->execute([
                'prenom' => trim($input['prenom']),
                'nom' => trim($input['nom']),
                'age' => isset($input['age']) ? (int)$input['age'] : null,
                'adresse' => trim($input['adresse'] ?? ''),
                'telephone' => trim($input['telephone'] ?? '')
            ]);
            
            $id = $db->lastInsertId();
            
            // Récupérer le patient créé
            $stmt = $db->prepare("SELECT * FROM patients WHERE id = :id");
            $stmt->execute(['id' => $id]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendSuccess($patient, 'Patient créé avec succès', 201);
            break;
            
        case 'PUT':
            // Modifier un patient
            if (!$id || !is_numeric($id)) {
                sendError('ID patient requis', 400);
            }
            
            validateRequired($input, ['prenom', 'nom']);
            
            $stmt = $db->prepare("UPDATE patients SET 
                prenom = :prenom, 
                nom = :nom, 
                age = :age, 
                adresse = :adresse, 
                telephone = :telephone,
                updated_at = NOW()
                WHERE id = :id");
            $stmt->execute([
                'id' => $id,
                'prenom' => trim($input['prenom']),
                'nom' => trim($input['nom']),
                'age' => isset($input['age']) ? (int)$input['age'] : null,
                'adresse' => trim($input['adresse'] ?? ''),
                'telephone' => trim($input['telephone'] ?? '')
            ]);
            
            if ($stmt->rowCount() === 0) {
                sendError('Patient non trouvé ou aucune modification', 404);
            }
            
            sendSuccess(null, 'Patient mis à jour avec succès');
            break;
            
        case 'DELETE':
            // Supprimer un patient
            if (!$id || !is_numeric($id)) {
                sendError('ID patient requis', 400);
            }
            
            // Vérifier si le patient a des dossiers
            $stmt = $db->prepare("SELECT COUNT(*) FROM dossiers WHERE patient_id = :id");
            $stmt->execute(['id' => $id]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                sendError('Impossible de supprimer ce patient car il a ' . $count . ' dossier(s) associé(s)', 409);
            }
            
            $stmt = $db->prepare("DELETE FROM patients WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                sendError('Patient non trouvé', 404);
            }
            
            sendSuccess(null, 'Patient supprimé avec succès');
            break;
            
        default:
            sendError('Méthode non autorisée', 405);
    }
}

// ============================================================
// ROUTE : DOSSIERS
// ============================================================
if ($resource === 'dossiers') {
    switch ($method) {
        case 'GET':
            if ($id && is_numeric($id)) {
                // Détail d'un dossier
                $stmt = $db->prepare("SELECT d.*, p.prenom, p.nom, p.age, p.adresse, p.telephone 
                                     FROM dossiers d
                                     LEFT JOIN patients p ON d.patient_id = p.id
                                     WHERE d.id = :id");
                $stmt->execute(['id' => $id]);
                $dossier = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$dossier) {
                    sendError('Dossier non trouvé', 404);
                }
                
                sendSuccess($dossier);
            } elseif ($id === 'search') {
                // Recherche de dossiers
                $keyword = isset($_GET['q']) ? trim($_GET['q']) : '';
                
                if (empty($keyword)) {
                    sendError('Terme de recherche requis', 400);
                }
                
                $stmt = $db->prepare("SELECT d.*, p.prenom, p.nom, p.age 
                                     FROM dossiers d
                                     LEFT JOIN patients p ON d.patient_id = p.id
                                     WHERE p.nom LIKE :keyword 
                                     OR p.prenom LIKE :keyword 
                                     OR d.diagnostic_entree LIKE :keyword
                                     OR d.resume LIKE :keyword
                                     ORDER BY d.created_at DESC");
                $stmt->execute(['keyword' => '%' . $keyword . '%']);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                sendSuccess($results);
            } else {
                // Liste des dossiers avec pagination
                $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $limit = isset($_GET['limit']) ? min(100, max(1, (int)$_GET['limit'])) : 20;
                $offset = ($page - 1) * $limit;
                
                // Filtrer par incident
                $incidentFilter = isset($_GET['incident']) ? (int)$_GET['incident'] : null;
                $whereClause = '';
                $params = [];
                
                if ($incidentFilter !== null) {
                    $whereClause = " WHERE d.incident = :incident";
                    $params['incident'] = $incidentFilter;
                }
                
                $stmt = $db->prepare("SELECT d.*, p.prenom, p.nom, p.age 
                                     FROM dossiers d
                                     LEFT JOIN patients p ON d.patient_id = p.id
                                     $whereClause
                                     ORDER BY d.created_at DESC
                                     LIMIT :limit OFFSET :offset");
                
                foreach ($params as $key => $value) {
                    $stmt->bindParam(":$key", $value);
                }
                $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
                $stmt->execute();
                $dossiers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $countQuery = "SELECT COUNT(*) FROM dossiers d" . $whereClause;
                $countStmt = $db->prepare($countQuery);
                foreach ($params as $key => $value) {
                    $countStmt->bindParam(":$key", $value);
                }
                $countStmt->execute();
                $total = $countStmt->fetchColumn();
                
                sendSuccess([
                    'items' => $dossiers,
                    'pagination' => [
                        'page' => $page,
                        'limit' => $limit,
                        'total' => (int)$total,
                        'totalPages' => ceil($total / $limit)
                    ]
                ]);
            }
            break;
            
        case 'POST':
            // Créer un dossier
            validateRequired($input, ['patient_id', 'date_entree']);
            
            // Vérifier que le patient existe
            $stmt = $db->prepare("SELECT id FROM patients WHERE id = :id");
            $stmt->execute(['id' => $input['patient_id']]);
            if (!$stmt->fetch()) {
                sendError('Patient non trouvé', 404);
            }
            
            $stmt = $db->prepare("INSERT INTO dossiers (
                patient_id, date_entree, mode_admission, heure_entree,
                diagnostic_entree, indication_cbt, date_cbt, heure_cbt,
                sexe_nne, poids_grammes, apgar_1, apgar_5, incident, resume
            ) VALUES (
                :patient_id, :date_entree, :mode_admission, :heure_entree,
                :diagnostic_entree, :indication_cbt, :date_cbt, :heure_cbt,
                :sexe_nne, :poids_grammes, :apgar_1, :apgar_5, :incident, :resume
            )");
            
            $stmt->execute([
                'patient_id' => (int)$input['patient_id'],
                'date_entree' => $input['date_entree'],
                'mode_admission' => $input['mode_admission'] ?? 'REF',
                'heure_entree' => $input['heure_entree'] ?? null,
                'diagnostic_entree' => trim($input['diagnostic_entree'] ?? ''),
                'indication_cbt' => trim($input['indication_cbt'] ?? ''),
                'date_cbt' => $input['date_cbt'] ?? null,
                'heure_cbt' => $input['heure_cbt'] ?? null,
                'sexe_nne' => $input['sexe_nne'] ?? null,
                'poids_grammes' => isset($input['poids_grammes']) ? (int)$input['poids_grammes'] : null,
                'apgar_1' => isset($input['apgar_1']) ? (int)$input['apgar_1'] : null,
                'apgar_5' => isset($input['apgar_5']) ? (int)$input['apgar_5'] : null,
                'incident' => isset($input['incident']) ? (int)$input['incident'] : 0,
                'resume' => trim($input['resume'] ?? '')
            ]);
            
            $id = $db->lastInsertId();
            
            // Récupérer le dossier créé
            $stmt = $db->prepare("SELECT d.*, p.prenom, p.nom 
                                 FROM dossiers d
                                 LEFT JOIN patients p ON d.patient_id = p.id
                                 WHERE d.id = :id");
            $stmt->execute(['id' => $id]);
            $dossier = $stmt->fetch(PDO::FETCH_ASSOC);
            
            sendSuccess($dossier, 'Dossier créé avec succès', 201);
            break;
            
        case 'DELETE':
            // Supprimer un dossier
            if (!$id || !is_numeric($id)) {
                sendError('ID dossier requis', 400);
            }
            
            $stmt = $db->prepare("DELETE FROM dossiers WHERE id = :id");
            $stmt->execute(['id' => $id]);
            
            if ($stmt->rowCount() === 0) {
                sendError('Dossier non trouvé', 404);
            }
            
            sendSuccess(null, 'Dossier supprimé avec succès');
            break;
            
        default:
            sendError('Méthode non autorisée', 405);
    }
}

// ============================================================
// ROUTE : STATISTIQUES
// ============================================================
if ($resource === 'stats') {
    if ($id === 'dashboard') {
        // Statistiques générales
        $totalDossiers = $db->query("SELECT COUNT(*) FROM dossiers")->fetchColumn();
        $totalPatients = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
        $incidents = $db->query("SELECT COUNT(*) FROM dossiers WHERE incident = 1")->fetchColumn();
        $poids = $db->query("SELECT AVG(poids_grammes) FROM dossiers WHERE poids_grammes IS NOT NULL")->fetchColumn();
        
        // Répartition par sexe
        $sexes = $db->query("SELECT sexe_nne, COUNT(*) as count FROM dossiers WHERE sexe_nne IS NOT NULL GROUP BY sexe_nne")->fetchAll(PDO::FETCH_ASSOC);
        
        // Répartition par mode d'admission
        $modes = $db->query("SELECT mode_admission, COUNT(*) as count FROM dossiers GROUP BY mode_admission")->fetchAll(PDO::FETCH_ASSOC);
        
        // Derniers dossiers
        $recent = $db->query("SELECT d.*, p.prenom, p.nom FROM dossiers d LEFT JOIN patients p ON d.patient_id = p.id ORDER BY d.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        
        // Admissions par mois (6 derniers mois)
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-$i months"));
        }
        
        $monthlyData = [];
        foreach ($months as $month) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM dossiers WHERE DATE_FORMAT(date_entree, '%Y-%m') = :month");
            $stmt->execute(['month' => $month]);
            $monthlyData[] = (int)$stmt->fetchColumn();
        }
        
        sendSuccess([
            'total_dossiers' => (int)$totalDossiers,
            'total_patients' => (int)$totalPatients,
            'total_incidents' => (int)$incidents,
            'poids_moyen' => round((float)$poids, 0),
            'repartition_sexe' => $sexes,
            'repartition_mode' => $modes,
            'derniers_dossiers' => $recent,
            'admissions_mensuelles' => [
                'months' => $months,
                'values' => $monthlyData
            ]
        ]);
    }
    
    sendError('Statistique non trouvée', 404);
}

// ============================================================
// ROUTE PAR DÉFAUT
// ============================================================
sendError('Route non trouvée', 404);
