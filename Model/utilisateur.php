<?php

   class user{
      private $id;
      private  $nom;
      private  $prenom;
      private  $email;
      private  $mot_de_passe;
      private  $type;
      private  $date_inscription;
      
      
      function __construct($id,$nom,$prenom,$email,$mot_de_passe,$type,$date_inscription){
        $this->id=$id;
         $this->nom=$nom;
         $this->prenom=$prenom;
         $this->email=$email;
         $this->mot_de_passe=$mot_de_passe;
         $this->type=$type;
         $this->date_inscription=$date_inscription;
      }
      
      public function setNom($nom) { $this->nom = $nom; }
    public function getNom() { return $this->nom; }

    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function getPrenom() { return $this->prenom; }

    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    public function setMotDePasse($mot_de_passe) { $this->mot_de_passe = $mot_de_passe; }
    public function getMotDePasse() { return $this->mot_de_passe; }

    public function setType($type) { $this->type = $type; }
    public function getType() { return $this->type; }

    public function setDateInscription($date_inscription) { $this->date_inscription = $date_inscription; }
    public function getDateInscription() { return $this->date_inscription; }

    public function setId($id) { $this->id_utilisateur = $id; }
    public function getId() { return $this->id_utilisateur; }
  

      
      
   }
   
      
?>