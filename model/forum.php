<?php
class Forum {
    private $id;
    private $titre;
    private $message;
    private $category;
    private $user_id;

    public function __construct($titre, $message, $category, $user_id) {
        $this->titre = $titre;
        $this->message = $message;
        $this->category = $category;
        $this->user_id = $user_id;
    }

    // Getters
    public function getId() {
        return $this->id;
    }
    public function getTitre() {
        return $this->titre;
    }
    public function getMessage() {
        return $this->message;
    }
    public function getCategory() {
        return $this->category;
    }
    public function getUserId() {
        return $this->user_id;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }
    public function setTitre($titre) {
        $this->titre = $titre;
    }
    public function setMessage($message) {
        $this->message = $message;
    }
    public function setCategory($category) {
        $this->category = $category;
    }
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }
}
?>