# Système de Gestion de Maternité

## Structure
- `api/` — Backend PHP (REST API)
- `frontend/` — Application Angular (build)
- `database/` — Scripts SQL (migrations + seeds)
- `logs/` — Journaux d'accès et d'erreurs
- `backup/` — Scripts et archives de sauvegarde

## Installation
1. Copier `.env.example` → `.env` et renseigner les variables
2. Importer `database/schema.sql` dans MySQL
3. `cd api && composer install`
4. Déployer `frontend/` sur le serveur web

## API
URL de base : `https://api.maternite.yourdomain.com`
Authentification : Bearer JWT
