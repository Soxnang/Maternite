-- ============================================
-- BASE DE DONNÉES MATERNITÉ DIGITAL
-- ============================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS maternite_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE maternite_db;

-- ============================================
-- TABLE DES PATIENTS
-- ============================================
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prenom VARCHAR(50) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    age INT,
    adresse VARCHAR(200),
    telephone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nom (nom),
    INDEX idx_prenom (prenom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE DES DOSSIERS
-- ============================================
CREATE TABLE IF NOT EXISTS dossiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    date_entree DATE NOT NULL,
    mode_admission ENUM('REF', 'VEM', 'AUTRE') DEFAULT 'REF',
    heure_entree TIME,
    diagnostic_entree TEXT,
    indication_cbt TEXT,
    date_cbt DATE,
    heure_cbt TIME,
    sexe_nne ENUM('M', 'F', 'Indéterminé'),
    poids_grammes INT,
    apgar_1 INT CHECK (apgar_1 >= 0 AND apgar_1 <= 10),
    apgar_5 INT CHECK (apgar_5 >= 0 AND apgar_5 <= 10),
    incident BOOLEAN DEFAULT FALSE,
    resume TEXT,
    date_sortie DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_date_entree (date_entree),
    INDEX idx_incident (incident)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE DES UTILISATEURS
-- ============================================
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user', 'viewer') DEFAULT 'user',
    email VARCHAR(100),
    nom_complet VARCHAR(100),
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLE DES LOGS
-- ============================================
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- DONNÉES INITIALES
-- ============================================

-- Utilisateur Admin (mot de passe: admin123)
INSERT IGNORE INTO utilisateurs (username, password_hash, role, email, nom_complet) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@maternite.local', 'Administrateur');

-- Utilisateur Test (mot de passe: password123)
INSERT IGNORE INTO utilisateurs (username, password_hash, role, email, nom_complet) 
VALUES ('user', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', 'user@maternite.local', 'Utilisateur Test');

-- ============================================
-- DONNÉES DE TEST - PATIENTS
-- ============================================
INSERT IGNORE INTO patients (prenom, nom, age, adresse, telephone) VALUES
('Yacine', 'Sall', 19, 'paoskoto', '779210722'),
('Mingué', 'Khouma', 32, 'Ndankhar Sokorong', NULL),
('Khady', 'Diallo', 23, NULL, NULL),
('Yama', 'Niang', 22, NULL, NULL),
('Fatou', 'Nguer', 22, 'Porokhane', NULL),
('Abibatou', 'Diallo', 37, 'Keur Ayib', '777134291'),
('Absa', 'Diallo', 19, 'Dinguiraye', '763785878'),
('Ndeye Waly', 'Diop', 26, 'Ndiobéne walo', '785752392'),
('Fatou', 'Ndiaye', 23, 'Dinguiraye', NULL),
('Ndeye Maty', 'Thiam', 40, 'Taiba Niassene', NULL),
('Diarry', 'BA', 20, 'Ndienguene', NULL),
('Aissatou Kenda', 'Diallo', 29, 'Ndiobene santhie', NULL),
('Gnoumba', 'Cissé', 18, 'Keur Bacary', '786714214'),
('Yidy', 'Diallo', 35, 'Peul Mory', NULL),
('Aissatou', 'Dieng', 16, 'Makka Dieng', '786514629'),
('Khodia', 'Seck', 25, 'Youndoulaye', '781731990'),
('Dialé', 'Touré', 30, 'Keur Ayib', '770397767'),
('Fatim', 'Diop', 21, 'Poste keur ayib', '770120067'),
('Fatou Awa', 'Diop', 20, 'Ndiobene', '783525779'),
('Amy', 'Ba', 19, 'Keur Ilo Ka', '779805312');

-- ============================================
-- DONNÉES DE TEST - DOSSIERS
-- ============================================
INSERT IGNORE INTO dossiers (patient_id, date_entree, mode_admission, heure_entree, diagnostic_entree, 
    indication_cbt, date_cbt, heure_cbt, sexe_nne, poids_grammes, apgar_1, apgar_5, incident, resume) VALUES
(1, '2025-01-12', 'REF', '03:26:00', 'phase active du travail/G de 39SA', 'EFNR', '2025-02-12', '03:26:00', 'F', 3203, 8, 9, 0, NULL),
(2, '2025-12-11', 'REF', '20:31:00', 'Suspicion HRP grade 3 en phase active du travail sur grossesse de 28SA+5', 'Laparotomie pour rupture utérine après expulsion', '2025-12-11', '20:31:00', 'F', 1685, NULL, NULL, 1, 'Laparotomie pour rupture utérine après expulsion permettant de faire une hystérectomie subtotale à visée d\'hémostase'),
(3, '2025-12-10', 'REF', '10:00:00', 'Cerclage pour béance cervico-isthmique sur grossesse de 24SA', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Cerclage pour béance cervico-isthmique sur grossesse de 24SA'),
(4, '2025-12-08', 'VEM', '14:30:00', 'Torsion du kyste de l\'ovaire gauche', 'Kystectomie gauche', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(5, '2025-12-07', 'REF', '09:15:00', 'GEU Ampullaire droite non Rompue', 'Salpingectomie droite', NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL),
(6, '2025-12-10', 'REF', '16:37:00', 'Suspicion HRP grade 2 sher sur G à terme', 'HRP grade 2 sher', '2025-12-10', '16:37:00', 'M', 3380, 7, 9, 0, NULL),
(7, '2025-12-11', 'REF', '00:26:00', 'RPM de 4h sur G de 40SA non en travail', 'Dystocie de démarrage sur RPM de 16h', '2025-12-12', '00:26:00', 'F', 2880, 8, 9, 0, 'La patiente aurait reçu deux poses de miso en IV sans entrée en travail'),
(8, '2025-12-09', 'REF', '20:15:00', 'HRP Grade 2 sher sur G de 36SA', 'HRP Grade 2 sher', '2025-12-09', '20:15:00', 'M', 2145, 8, 9, 0, NULL),
(9, '2025-12-08', 'REF', '21:01:00', 'DDT/UC non en travail', 'DDT 43SA/UC', '2025-12-08', '21:01:00', 'F', 3100, 8, 9, 0, NULL),
(10, '2025-12-24', 'REF', '17:06:00', 'RPM de 4h sur HTA Gravidique sévère', 'Laparotomie pour Rupture utérine', '2025-12-24', '17:06:00', 'F', NULL, NULL, NULL, 1, 'Rupture utérine avec propagation pédiculaire et cervicale permettant de réaliser une hystérectomie d\'hémostase'),
(11, '2025-12-24', 'REF', '01:37:00', 'DDT primi geste', 'CBT/EFNR/DDT', '2025-12-25', '01:37:00', 'M', 3400, 7, 8, 0, 'RAS'),
(12, '2025-12-25', 'REF', '06:14:00', 'UBC en travail', 'CBT pour UBC en Travail', '2025-12-25', '06:14:00', 'M', 2360, 8, 9, 0, 'Petite rupture séreuse notée au niveau de la partie antérieure du corps utérin'),
(13, '2025-12-24', 'VEM', '00:03:00', 'BGR en travail', 'CBT/BGR en Travail/Primi', '2025-12-25', '00:03:00', 'F', 2450, 8, 9, 0, NULL),
(14, '2025-12-24', 'REF', '20:50:00', 'RPM de 24h/Utérus cicatriciel', 'DFP/UC', '2025-12-24', '20:50:00', 'M', 2250, 7, 9, 0, 'Césarienne systématique à la prochaine grossesse'),
(15, '2025-12-26', 'VEM', '13:13:00', 'Bassin Immature primi', 'CBT Proph/Bassin immature', '2025-12-26', '13:13:00', 'F', 3180, 8, 9, 0, 'Scannopelvimétrie à faire'),
(16, '2025-12-26', 'VEM', '11:13:00', 'Utérus bicicatriciel non en travail', 'CBT Proph/Utérus bicicatriciel / G de 37SA', '2025-12-26', '11:13:00', 'M', NULL, 8, 7, 0, NULL),
(17, '2025-12-26', 'REF', '14:10:00', 'Phase active du travail /GG de 38SA', 'CBT/Collision J1 siège et J2 céphalique', '2025-12-26', '14:10:00', 'M', 2130, 8, 9, 0, 'J2 14h11 - Sexe masculin - Poids: 2530g - Présentation céphalique'),
(18, '2025-12-02', 'REF', '15:06:00', 'RPM de 10h primi siège non en travail', 'EFNR/Primi siège', '2025-12-02', '15:06:00', 'M', 3170, 8, 9, 0, NULL),
(19, '2025-12-02', 'REF', '01:25:00', 'Primi siège sur G de 39SA+3j', 'Dilatation stationnaire', '2025-12-02', '01:25:00', 'F', 2930, 7, 9, 0, NULL),
(20, '2025-12-24', 'REF', '19:34:00', 'Phase de latence du G de 38SA/BL', 'CBT/Dystocie de démarrage /BL', '2025-12-24', '19:34:00', 'M', 3250, 8, 9, 0, 'Scannopelvimétrie à faire à la prochaine grossesse');

-- ============================================
-- VÉRIFICATIONS FINALES
-- ============================================
SELECT '=== BASE DE DONNÉES CRÉÉE AVEC SUCCÈS ===' as message;
SELECT CONCAT('Patients: ', COUNT(*)) as info FROM patients;
SELECT CONCAT('Dossiers: ', COUNT(*)) as info FROM dossiers;
SELECT CONCAT('Utilisateurs: ', COUNT(*)) as info FROM utilisateurs;
