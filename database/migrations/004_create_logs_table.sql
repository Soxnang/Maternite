CREATE TABLE IF NOT EXISTS logs (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id      INT,
    action              VARCHAR(50) NOT NULL,
    table_cible         VARCHAR(50),
    enregistrement_id   INT,
    details             JSON,
    ip_address          VARCHAR(45),
    created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB;
