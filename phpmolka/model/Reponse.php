<?php

class Reponse
{
    private $id_reponse;
    private $id_reclamation_reponse;
    private $id_user_reponse;
    private $description_reponse;
    private $date_reponse;

    // Constructeur
    public function __construct($id_reclamation_reponse, $id_user_reponse, $description_reponse, $date_reponse)
    {
        $this->id_reclamation_reponse = $id_reclamation_reponse;
        $this->id_user_reponse = $id_user_reponse;
        $this->description_reponse = $description_reponse;
        $this->date_reponse = $date_reponse;
    }

    // Getters et Setters
    public function getIdReponse()
    {
        return $this->id_reponse;
    }

    public function setIdReponse($id_reponse)
    {
        $this->id_reponse = $id_reponse;
    }

    public function getIdReclamationReponse()
    {
        return $this->id_reclamation_reponse;
    }

    public function setIdReclamationReponse($id_reclamation_reponse)
    {
        $this->id_reclamation_reponse = $id_reclamation_reponse;
    }

    public function getIdUserReponse()
    {
        return $this->id_user_reponse;
    }

    public function setIdUserReponse($id_user_reponse)
    {
        $this->id_user_reponse = $id_user_reponse;
    }

    public function getDescriptionReponse()
    {
        return $this->description_reponse;
    }

    public function setDescriptionReponse($description_reponse)
    {
        $this->description_reponse = $description_reponse;
    }

    public function getDateReponse()
    {
        return $this->date_reponse;
    }

    public function setDateReponse($date_reponse)
    {
        $this->date_reponse = $date_reponse;
    }
}
?>
