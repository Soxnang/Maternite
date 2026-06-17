<?php
namespace App\Models;

use PDO;

class Dossier {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getAll($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT d.*, p.prenom, p.nom, p.age, p.adresse, p.telephone 
                  FROM dossiers d
                  LEFT JOIN patients p ON d.patient_id = p.id
                  ORDER BY d.created_at DESC
                  LIMIT :offset, :limit";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTotal() {
        $query = "SELECT COUNT(*) as total FROM dossiers";
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function getOne($id) {
        $query = "SELECT d.*, p.prenom, p.nom, p.age, p.adresse, p.telephone 
                  FROM dossiers d
                  LEFT JOIN patients p ON d.patient_id = p.id
                  WHERE d.id = :id";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $query = "INSERT INTO dossiers (
                    patient_id, date_entree, mode_admission, heure_entree,
                    diagnostic_entree, indication_cbt, date_cbt, heure_cbt,
                    sexe_nne, poids_grammes, apgar_1, apgar_5,
                    incident, resume, date_sortie
                  ) VALUES (
                    :patient_id, :date_entree, :mode_admission, :heure_entree,
                    :diagnostic_entree, :indication_cbt, :date_cbt, :heure_cbt,
                    :sexe_nne, :poids_grammes, :apgar_1, :apgar_5,
                    :incident, :resume, :date_sortie
                  )";
                  
        $stmt = $this->conn->prepare($query);
        
        // Nettoyage et binding
        $stmt->bindParam(':patient_id', $data['patient_id']);
        $stmt->bindParam(':date_entree', $data['date_entree']);
        $stmt->bindParam(':mode_admission', $data['mode_admission']);
        $stmt->bindParam(':heure_entree', $data['heure_entree']);
        $stmt->bindParam(':diagnostic_entree', $data['diagnostic_entree']);
        $stmt->bindParam(':indication_cbt', $data['indication_cbt']);
        $stmt->bindParam(':date_cbt', $data['date_cbt']);
        $stmt->bindParam(':heure_cbt', $data['heure_cbt']);
        $stmt->bindParam(':sexe_nne', $data['sexe_nne']);
        $stmt->bindParam(':poids_grammes', $data['poids_grammes']);
        $stmt->bindParam(':apgar_1', $data['apgar_1']);
        $stmt->bindParam(':apgar_5', $data['apgar_5']);
        $stmt->bindParam(':incident', $data['incident']);
        $stmt->bindParam(':resume', $data['resume']);
        $stmt->bindParam(':date_sortie', $data['date_sortie']);
        
        $stmt->execute();
        return $this->conn->lastInsertId();
    }
    
    public function update($id, $data) {
        $query = "UPDATE dossiers SET
                    patient_id = :patient_id,
                    date_entree = :date_entree,
                    mode_admission = :mode_admission,
                    heure_entree = :heure_entree,
                    diagnostic_entree = :diagnostic_entree,
                    indication_cbt = :indication_cbt,
                    date_cbt = :date_cbt,
                    heure_cbt = :heure_cbt,
                    sexe_nne = :sexe_nne,
                    poids_grammes = :poids_grammes,
                    apgar_1 = :apgar_1,
                    apgar_5 = :apgar_5,
                    incident = :incident,
                    resume = :resume,
                    date_sortie = :date_sortie
                  WHERE id = :id";
                  
        $stmt = $this->conn->prepare($query);
        
        // Binding
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':patient_id', $data['patient_id']);
        $stmt->bindParam(':date_entree', $data['date_entree']);
        $stmt->bindParam(':mode_admission', $data['mode_admission']);
        $stmt->bindParam(':heure_entree', $data['heure_entree']);
        $stmt->bindParam(':diagnostic_entree', $data['diagnostic_entree']);
        $stmt->bindParam(':indication_cbt', $data['indication_cbt']);
        $stmt->bindParam(':date_cbt', $data['date_cbt']);
        $stmt->bindParam(':heure_cbt', $data['heure_cbt']);
        $stmt->bindParam(':sexe_nne', $data['sexe_nne']);
        $stmt->bindParam(':poids_grammes', $data['poids_grammes']);
        $stmt->bindParam(':apgar_1', $data['apgar_1']);
        $stmt->bindParam(':apgar_5', $data['apgar_5']);
        $stmt->bindParam(':incident', $data['incident']);
        $stmt->bindParam(':resume', $data['resume']);
        $stmt->bindParam(':date_sortie', $data['date_sortie']);
        
        return $stmt->execute();
    }
    
    public function delete($id) {
        $query = "DELETE FROM dossiers WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function search($keyword) {
        $query = "SELECT d.*, p.prenom, p.nom 
                  FROM dossiers d
                  LEFT JOIN patients p ON d.patient_id = p.id
                  WHERE p.nom LIKE :keyword 
                  OR p.prenom LIKE :keyword 
                  OR d.diagnostic_entree LIKE :keyword
                  OR d.resume LIKE :keyword
                  ORDER BY d.created_at DESC";
                  
        $keyword = "%{$keyword}%";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
