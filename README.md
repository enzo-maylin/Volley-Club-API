# Projet Symfony

Bienvenue dans ce projet Symfony ! Ce README vous guide à travers les étapes pour initialiser et configurer le projet après un `git clone`.

## Installation

### 1. Cloner le dépôt

```bash
git clone https://gitlabinfo.iutmontp.univ-montp2.fr/mayline/volley_club_api
cd volley_club_api
```
vivant
### 2. Installer les dépendances

Utilisez Composer pour installer les dépendances PHP :

```bash
composer install
```

### 3. Configurer les variables d'environnement

Dupliquez le fichier `.env` en `.env.local` pour vos configurations locales :

```bash
cp .env .env.local
```

Ouvrez le fichier `.env.local` et configurez les informations de votre base de données :

```dotenv
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/nom_de_la_base_de_donnees?serverVersion=5.7"
```

Remplacez `db_user`, `db_password`, `127.0.0.1:3306`, et `nom_de_la_base_de_donnees` par vos propres informations.

### 4. Créer la base de données

Générez la base de données avec la commande suivante :

```bash
php bin/console doctrine:database:create
```

### 5. Exécuter les migrations

Appliquez les migrations pour créer les tables nécessaires dans la base de données :

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Générer les clés JWT

Comme on utilise le bundle LexikJWTAuthenticationBundle pour l'authentification, générez les clés JWT avec la commande suivante :

```bash
php bin/console lexik:jwt:generate-keypair
```

---

## Le fonctionnement de l'API

Cette API permet la gestion des événements pour les clubs de volley. Voici un aperçu de son fonctionnement :

### Rôles des utilisateurs

- **Coach** (`ROLE_COACH`):
  - Un coach peut créer un club, qui lui est attribué (automatiqument), en spécifiant le nom du club **POST** `/api/clubs`.
  - Un coach peut supprimer seulement son club qu'il a créé **DELETE** `/api/clubs/{id}`.
  - Si le compte du coach est supprimé, le club associé sera également supprimé.
  - Le coach peut créer des équipes pour son club **POST** `/api/equipes`.
  - Le coach peut modifier et supprimer les équipes qu'il a crééb **PATCH** **DELETE** `/api/equipes/{id}`
  - Le coach peut inscrire une de ses équipes à un événement en utilisant la méthode **PUT** `/api/equipes/{idEquipe}/evenements/{idEvenement}`.
  - Seul le coach du club peut inscrire ou désinscrire ses équipes des événements **DELETE** `/api/equipes/{idEquipe}/evenements/{idEvenement}`.
  - Il peux y avoir un nombre maximum d'équipes pouvant participer à un événement.
  - Une équipe ne peut pas être inscrite à deux événements simultanément.

- **Organisateur** (`ROLE_ORGANISATEUR`):
  - Un organisateur peut créer des événements **POST** `/api/evenements`.
  - Il peut supprimer ses évènements et modifier les informations non critiques, comme :
    - La visibilité de la liste des participants (publique ou privée) lors de la lecture d'un évènement. 
    - Le montant du cash prize. **DELETE** **PATCH** `/api/evenements/{id}`
  - Les organisateurs ne peuvent pas modifier des informations critiques, telles que l'adresse ou les dates de début et de fin de l'événement, pour éviter des désagréments aux équipes déjà inscrites.

- **Administrateur** (`ROLE_ADMIN`):
  - Un administrateur a le pouvoir de supprimer n'importe quel utilisateur ainsi que n'importe quel événement. 

- **Utilisateur** (`ROLE_USER`):
  - Un utilisateur peux créer/modifer/supprimer son profil (login, adresse mail, mdp...).
  - Il peut  s'identifier via  **POST** `/api/auth`.
  - Lorsqu'un utilisateur accède à tous les évènements, il n'y a pas ceux où la date de début est dépassée **GET** `/api/evenements`.
  - Aussi, il peut choisir son équipe en modifiant son profil avec l'id de l'équipe (et pas qu'avec l'IRI) **PATCH** `/api/utilisateurs/{id}`

### Routes de Collection 
  
  - Liste des équipes d'un club **GET** `/api/clubs/{idClub}/equipes`
  - Liste des évènements organisés par un utilisateur (avec le role ROLE_ORGANISATEUR) **GET** `/api/utilisateurs/{idUtilisateur}/evenementsOrganises`
  - Liste des joueurs d'une équipe **GET** `/api/equipes/{idEquipe}/joueurs`

### Flux de travail

1. **Création de club** : Le coach crée un club via l'API.
2. **Création d'équipes** : Le coach crée des équipes qui appartiennent à son club.
3. **Inscription des utilisateurs** : Les utilisateurs peuvent s'inscrire à une équipe de leur choix.
4. **Création des événements** : Les organisateurs créent des événements via l'API.
5. **Inscription aux événements** : Le coach inscrit ses équipes à des événements, en respectant les contraintes d'inscription.
6. **Gestion des événements** : L'organisateur gère ses événements et peut les modifier dans les limites définies.

---

## Commandes utiles

Quelques commandes qui peuvent vous être utiles pour gérer le projet :

- **Nettoyer le cache** :

  ```bash
  php bin/console cache:clear
  ```

---

## Investissement de chaque membre

Projet créé par **Léanne BAVOILLOT, Mathys CAPO et Enzo MAYLIN**.

Nous avons tous travaillé autant sur le projet. 
La plupart du temps à plusieurs sur la même machine.
