<?php
class Forum {
    private $id;
    private $titre;
    private $message;
    private $category;
    private $user_id;
    private $image;
    private $message_image;

    public function __construct($titre, $message, $category, $user_id, $image = null, $message_image = null) {
        $this->titre = $titre;
        $this->message = $message;
        $this->category = $category;
        $this->user_id = $user_id;
        $this->image = $image;
        $this->message_image = $message_image;
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

    public function getImage() {
        return $this->image;
    }

    public function getMessageImage() {
        return $this->message_image;
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

    public function setImage($image) {
        $this->image = $image;
    }

    public function setMessageImage($message_image) {
        $this->message_image = $message_image;
    }
}
?>