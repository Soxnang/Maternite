-- Schéma complet base de données Maternité
CREATE DATABASE IF NOT EXISTS maternite CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE maternite;
SOURCE migrations/001_create_patients_table.sql;
SOURCE migrations/002_create_dossiers_table.sql;
SOURCE migrations/003_create_utilisateurs_table.sql;
SOURCE migrations/004_create_logs_table.sql;
SOURCE seeds/seed_utilisateurs.sql;
