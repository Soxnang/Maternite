CREATE TABLE IF NOT EXISTS dossiers (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    patient_id      INT NOT NULL,
    date_admission  DATE NOT NULL,
    date_sortie     DATE,
    diagnostic      TEXT,
    traitement      TEXT,
    statut          ENUM('ouvert','fermé','archivé') DEFAULT 'ouvert',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
) ENGINE=InnoDB;
