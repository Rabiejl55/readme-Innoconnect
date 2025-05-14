<?php
class Contract {
    private $id;
    private $user_id;
    private $nom;
    private $description;
    private $type;
    private $created_at;
    private $updated_at;
    private $status; // Add status property

    public function __construct($nom, $description, $type, $user_id = null, $status = 'pending') {
        $this->nom = $nom;
        $this->description = $description;
        $this->type = $type;
        $this->user_id = $user_id;
        $this->status = $status; // Initialize status
    }

    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getNom() { return $this->nom; }
    public function getDescription() { return $this->description; }
    public function getType() { return $this->type; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getStatus() {
        return $this->status;
    }
    public function setId($id) { $this->id = $id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setDescription($description) { $this->description = $description; }
    public function setType($type) { $this->type = $type; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
}
?>