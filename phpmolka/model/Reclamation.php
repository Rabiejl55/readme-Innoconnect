<?php

class Reclamation
{
    private $id;
    private $date;
    private $description;
    private $etat;

    // Constructeur
    public function __construct($date = null, $description = null, $etat = null)
    {
        $this->date = $date;
        $this->description = $description;
        $this->etat = $etat;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getEtat()
    {
        return $this->etat;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setEtat($etat)
    {
        $this->etat = $etat;
    }
}
?>