<?php
class AuthController {
    public function login(array $data): array {
        // TODO: valider credentials et retourner JWT
        return ['token' => 'jwt_token_here'];
    }
    public function logout(): array {
        return ['message' => 'Déconnexion réussie'];
    }
}
