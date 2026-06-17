<?php
namespace App\Middleware;

use App\Config\Auth;

class AuthMiddleware {
    public static function authenticate() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Token non fourni']);
            exit();
        }
        
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        
        try {
            $decoded = Auth::verifyToken($token);
            return $decoded;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(['error' => 'Token invalide']);
            exit();
        }
    }
    
    public static function requireRole($role) {
        $user = self::authenticate();
        if ($user['role'] !== $role) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            exit();
        }
        return $user;
    }
}
?>
