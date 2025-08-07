# Hammecones - Application de Pêche

Hammecones est une application de pêche qui permet aux utilisateurs de découvrir et de partager des spots de pêche, d'enregistrer leurs prises, et de consulter des informations sur différentes espèces de poissons.

## Technologies utilisées

- **Backend**: Laravel 12
- **Frontend**: Vue.js
- **CSS**: Tailwind CSS
- **Cartographie**: MapBox

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- Node.js et npm
- MySQL ou MariaDB

## Installation

1. Clonez le dépôt :
   ```
   git clone https://github.com/votre-utilisateur/hammecones.git
   cd hammecones
   ```

2. Installez les dépendances PHP :
   ```
   composer install
   ```

3. Installez les dépendances JavaScript :
   ```
   npm install
   ```

4. Copiez le fichier d'environnement et configurez-le :
   ```
   cp .env.example .env
   ```

5. Générez une clé d'application :
   ```
   php artisan key:generate
   ```

6. Configurez votre base de données dans le fichier `.env` :
   ```
   DB_CONNECTION=mysql
   DB_HOST=votre_hote_de_base_de_donnees
   DB_PORT=3306
   DB_DATABASE=nom_de_votre_base_de_donnees
   DB_USERNAME=votre_nom_utilisateur
   DB_PASSWORD=votre_mot_de_passe
   ```

7. Configurez votre token MapBox dans le fichier `.env` :
   ```
   VITE_MAPBOX_TOKEN=votre-token-mapbox
   ```

8. Exécutez les migrations pour créer les tables dans la base de données :
   ```
   php artisan migrate
   ```

9. (Optionnel) Remplissez la base de données avec des données de test :
   ```
   php artisan db:seed
   ```

## Démarrage de l'application

1. Démarrez le serveur de développement Laravel :
   ```
   php artisan serve
   ```

2. Dans un autre terminal, démarrez le serveur de développement Vite pour le frontend :
   ```
   npm run dev
   ```

3. Accédez à l'application dans votre navigateur à l'adresse `http://localhost:8000`

## Fonctionnalités

- Carte interactive des spots de pêche
  - Menu déroulant pour sélectionner parmi plusieurs styles de carte (rues, extérieur, clair, sombre, satellite, satellite avec noms)
  - Vue satellite avec noms des villes et villages par défaut
  - Zoom optimisé sur le Finistère Nord pour une meilleure vue d'ensemble
  - Contrôles de carte adaptés (zoom +/-, centrer sur ma position)
  - Navigation et géolocalisation intégrées
  - Ajout de nouveaux spots de pêche en cliquant sur la carte
  - Formulaire modal pour enregistrer les informations du spot:
    - Nom, description et commentaires
    - Sélection du type d'eau avec possibilité d'ajouter de nouveaux types
    - Sélection des espèces de poissons présentes avec possibilité d'ajouter de nouvelles espèces
- Gestion des espèces de poissons
- Enregistrement des prises
- Gestion de l'équipement de pêche
- Recherche et filtrage des spots et des espèces

## Structure de la base de données

- **users**: Utilisateurs de l'application
- **fishing_spots**: Spots de pêche avec coordonnées géographiques
- **fish_species**: Espèces de poissons
- **fishing_spot_fish_species**: Table pivot pour la relation many-to-many entre spots de pêche et espèces
- **catches**: Prises enregistrées par les utilisateurs
- **equipment**: Équipement de pêche des utilisateurs

## Commandes utiles

- Créer un contrôleur : `php artisan make:controller NomController`
- Créer un modèle : `php artisan make:model Nom`
- Créer une migration : `php artisan make:migration create_nom_table`
- Exécuter les migrations : `php artisan migrate`
- Annuler les migrations : `php artisan migrate:rollback`
- Vider le cache : `php artisan cache:clear`
- Générer des données de test : `php artisan db:seed`
