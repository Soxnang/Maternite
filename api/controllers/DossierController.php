<?php
namespace App\Controllers;

use App\Models\Dossier;
use App\Helpers\ResponseHelper;
use App\Middleware\AuthMiddleware;

class DossierController {
    private $db;
    private $dossierModel;
    
    public function __construct($db) {
        $this->db = $db;
        $this->dossierModel = new Dossier($db);
        // Vérifier l'authentification
        AuthMiddleware::authenticate();
    }
    
    public function getAll($page = 1, $limit = 20) {
        try {
            $result = $this->dossierModel->getAll($page, $limit);
            $total = $this->dossierModel->getTotal();
            
            return ResponseHelper::success([
                'data' => $result,
                'pagination' => [
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'total' => $total,
                    'totalPages' => ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
    
    public function getOne($id) {
        try {
            $result = $this->dossierModel->getOne($id);
            if ($result) {
                return ResponseHelper::success($result);
            }
            return ResponseHelper::error('Dossier non trouvé', 404);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
    
    public function create($data) {
        try {
            // Validation des données
            $this->validateDossier($data);
            
            // Création du dossier
            $id = $this->dossierModel->create($data);
            
            return ResponseHelper::success([
                'id' => $id,
                'message' => 'Dossier créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }
    
    public function update($id, $data) {
        try {
            // Vérifier si le dossier existe
            if (!$this->dossierModel->getOne($id)) {
                return ResponseHelper::error('Dossier non trouvé', 404);
            }
            
            // Validation des données
            $this->validateDossier($data);
            
            // Mise à jour
            $this->dossierModel->update($id, $data);
            
            return ResponseHelper::success([
                'message' => 'Dossier mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 400);
        }
    }
    
    public function delete($id) {
        try {
            // Vérifier si le dossier existe
            if (!$this->dossierModel->getOne($id)) {
                return ResponseHelper::error('Dossier non trouvé', 404);
            }
            
            // Suppression
            $this->dossierModel->delete($id);
            
            return ResponseHelper::success([
                'message' => 'Dossier supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
    
    public function search($keyword) {
        try {
            $result = $this->dossierModel->search($keyword);
            return ResponseHelper::success($result);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage());
        }
    }
    
    private function validateDossier($data) {
        $required = ['patient_id', 'date_entree', 'diagnostic_entree'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Le champ '$field' est requis");
            }
        }
        
        // Validation des Apgar
        if (isset($data['apgar_1']) && ($data['apgar_1'] < 0 || $data['apgar_1'] > 10)) {
            throw new \Exception("L'Apgar 1' doit être entre 0 et 10");
        }
        if (isset($data['apgar_5']) && ($data['apgar_5'] < 0 || $data['apgar_5'] > 10)) {
            throw new \Exception("L'Apgar 5' doit être entre 0 et 10");
        }
        
        // Validation du poids
        if (isset($data['poids_grammes']) && $data['poids_grammes'] < 0) {
            throw new \Exception("Le poids doit être positif");
        }
    }
}
?>
