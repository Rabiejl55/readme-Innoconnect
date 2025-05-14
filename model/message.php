<?php
class Message {
    private $id;
    private $forum_id;
    private $user_id;
    private $message;
    private $date_creation;

    public function __construct($forum_id, $user_id, $message) {
        $this->forum_id = $forum_id;
        $this->user_id = $user_id;
        $this->message = $message;
        $this->date_creation = date('Y-m-d H:i:s'); // Date actuelle lors de la création du message
    }

    // Getters
    public function getId() { return $this->id; }
    public function getForumId() { return $this->forum_id; }
    public function getUserId() { return $this->user_id; }
    public function getMessage() { return $this->message; }
    public function getDateCreation() { return $this->date_creation; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setForumId($forum_id) { $this->forum_id = $forum_id; }
    public function setUserId($user_id) { $this->user_id = $user_id; }
    public function setMessage($message) { $this->message = $message; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }
}
?>
