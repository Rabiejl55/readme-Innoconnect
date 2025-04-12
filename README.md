# readme-Innoconnect
 # InnoConnect

## Description du Projet
InnoConnect est une plateforme web qui permet aux innovateurs de soumettre leurs projets et aux investisseurs de les financer. Grâce à l'intelligence artificielle et au développement durable, la plateforme favorise la collaboration et l'émergence d'idées innovantes.

## Table des Matières
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Contribution](#contribution)
- [Licence](#licence)

## Installation
Installation

1. Cloner le repository :


git clone https://github.com/Rabiejl55/readme-Innoconnect.git
cd innoconnect

2. Configuration avec WAMP/XAMPP :


Placez le projet dans le dossier www (WAMP) ou htdocs (XAMPP).

Démarrez Apache et MySQL depuis l'interface de WAMP/XAMPP.

Accédez au projet via : http://localhost/innoconnect

3. Création de la base de données :

Importez le fichier SQL fourni (innoconnect.sql) dans phpMyAdmin.

Assurez-vous que les informations de connexion à la base de données sont correctes dans config.php.

## Utilisation
Installation de PHP

Pour utiliser InnoConnect, vous devez avoir PHP installé sur votre machine.

1. Téléchargez PHP à partir du site officiel : PHP - Téléchargement.

2. Installez PHP selon votre système d'exploitation :

Windows : Utilisez XAMPP ou WampServer.

macOS : Installez avec Homebrew :
brew install php

Linux : Installez via le gestionnaire de paquets (exemple Ubuntu) :
sudo apt update
sudo apt install php

3. Vérifiez l'installation de PHP :
php -v

Fonctionnalités principales :

* Innovateur :

Créer et soumettre un projet.

Gérer ses projets existants (modifier, supprimer).

Interagir avec d'autres utilisateurs dans l'espace collaboratif.

* Investisseur :

Parcourir les projets soumis.

Investir dans des projets.

Suivre ses transactions financières.

* Administrateur :

Valider les comptes des utilisateurs.

Superviser et gérer les interactions et transactions sur la plateforme.

### Connexion / Inscription

1. Pour vous inscrire, cliquez sur "S'inscrire" en haut à droite de la page d'accueil et renseignez :

- Nom

- Prénom

- Adresse e-mail

- Mot de passe

- Confirmation du mot de passe

2. Connectez-vous avec votre adresse e-mail et votre mot de passe.

3. Accédez à votre profil pour gérer vos informations.

### Déconnexion

Cliquez sur le bouton "Déconnexion" dans le menu pour quitter votre session en toute sécurité.

## Contribution
Nous remercions tous ceux qui ont contribué à ce projet !
### Contributeurs

Les personnes suivantes ont participé au développement d'InnoConnect :
- [Utilisateur1](https://github.com/faryoula11) - Gestion des utilisateurs (Inscription, connexion, gestion des rôles, sécurité des comptes).
- [Utilisateur2](https://github.com/Rabiejl55) - Espace collaboratif (Interaction en temps réel, forums de discussion, partage d'idées).
- [Utilisateur3](https://github.com/chakroun-nermine) - Transactions financières (Paiements sécurisés, suivi des investissements).
- [Utilisateur4](https://github.com/Moha200344) - Gestion des projets (Création, modification, suppression, catégorisation des projets).
- [Utilisateur5](https://github.com/molkaezzine) - Gestion des interactions (Modération des échanges, système de feedback).


Si vous souhaitez contribuer, suivez les étapes ci-dessous pour faire un *fork*, créer une nouvelle branche et soumettre une *pull request*.

### Comment contribuer ?

1. *Fork le projet* : Allez sur la page GitHub du projet et cliquez sur le bouton *Fork* dans le coin supérieur droit pour créer une copie du projet dans votre propre compte GitHub.
   
2. *Clonez votre fork* : Clonez le fork sur votre machine locale :
   ```bash
   git clone https://github.com/Rabiejl55/readme-Innoconnect.git
   cd InnoConnect
3. *Créer une nouvelle branche* : 
   git checkout -b nouvelle-fonctionnalite

4. *Faire vos modifications et commiter* : 
   git commit -m "Ajout d'une nouvelle fonctionnalité"

5. *Pusher votre branche* :
   git push -u origin nouvelle-fonctionnalite

6. *Soumettre une pull request* : Allez sur votre fork GitHub et cliquez
sur le bouton *New pull request*. Sélectionnez la branche que vous avez
créée et soumettez votre pull request.

### Licence
Ce projet est sous la licence *MIT*. 
Pour plus de détails, consultez le fichier[MIT](https://choosealicense.com/licenses/mit/).
