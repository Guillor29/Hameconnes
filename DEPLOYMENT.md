# Déploiement de l'Application Les Hameçonnés

Ce document explique comment déployer l'application Les Hameçonnés en production en utilisant GitHub Actions, Docker et FTP.

## Prérequis

- Un compte GitHub avec le code source de l'application
- Un compte Docker Hub pour stocker l'image Docker
- Un hébergement O2switch avec accès FTP et SSH
- Les secrets GitHub configurés (voir ci-dessous)

## Configuration des Secrets GitHub dans l'Environnement Dev

Pour que le déploiement fonctionne correctement, vous devez configurer les secrets suivants dans l'environnement **Dev** de votre dépôt GitHub (et non pas comme secrets de dépôt) :

### Configuration de l'Environnement Dev

1. Accédez aux paramètres de votre dépôt GitHub (onglet "Settings")
2. Dans le menu latéral, cliquez sur "Environments"
3. Cliquez sur "New environment"
4. Nommez l'environnement "Dev" (respectez la casse)
5. Cliquez sur "Configure environment"
6. Dans la section "Environment secrets", ajoutez les secrets listés ci-dessous

### Secrets de Base de Données

- `DB_HOST` : L'hôte de la base de données (ex: 109.234.165.93)
- `DB_PORT` : Le port de la base de données (ex: 3306)
- `DB_NAME` : Le nom de la base de données (ex: bfps0361_Hameconnes)
- `DB_USER` : L'utilisateur de la base de données (ex: bfps0361_Hameconnes)
- `DB_PASS` : Le mot de passe de la base de données (ex: #!Perc!29260!#)
- `DB_CHARSET` : Le jeu de caractères de la base de données (ex: utf8mb4)

### Secrets FTP

- `FTP_SERVER` : Le serveur FTP (ex: ftp.bfps0361.odns.fr)
- `FTP_PORT` : Le port FTP (ex: 21)
- `FTP_USER` : L'utilisateur FTP (ex: hameconnes@hameconnes.guillaume-rv.fr)
- `FTP_PASSWORD` : Le mot de passe FTP
- `FTP_IP` : L'adresse IP du serveur pour SSH (ex: 109.234.165.93)

### Secrets Docker

- `DOCKER_USER` : Votre nom d'utilisateur Docker Hub (ex: guillor)
- `DOCKER_TOKEN` : Votre token d'accès Docker Hub

## Comment fonctionne le déploiement

Le workflow GitHub Actions est configuré pour se déclencher automatiquement lorsque vous poussez du code sur la branche `main`, ou manuellement via l'interface GitHub Actions. Le workflow utilise l'environnement **Dev** pour accéder aux secrets nécessaires au déploiement.

Le processus de déploiement comprend les étapes suivantes :

1. **Checkout du code** : Récupération du code source depuis GitHub
2. **Configuration de Docker** : Préparation de l'environnement Docker
3. **Connexion à Docker Hub** : Authentification avec les identifiants Docker Hub
4. **Build et push de l'image Docker** : Construction de l'image Docker et envoi vers Docker Hub
5. **Génération du fichier .env** : Création d'un fichier .env pour la production
6. **Déploiement via FTP** : Transfert des fichiers vers le serveur O2switch
7. **Configuration finale sur le serveur** : Renommage du fichier .env, mise en cache des configurations et exécution des migrations

## Structure Docker

Le projet utilise Docker pour la conteneurisation :

- **Dockerfile** : Définit l'image Docker pour l'application Laravel
- **docker-compose.yml** : Configure les services pour le développement local
- **nginx/conf.d/app.conf** : Configuration Nginx pour servir l'application
- **php/local.ini** : Configuration PHP personnalisée

## Déclenchement manuel du déploiement

Pour déclencher manuellement un déploiement :

1. Allez dans l'onglet "Actions" de votre dépôt GitHub
2. Sélectionnez le workflow "Deploy to Production"
3. Cliquez sur "Run workflow"
4. Sélectionnez la branche (généralement `main`)
5. Cliquez sur "Run workflow"

## Vérification du déploiement

Après le déploiement, vous pouvez vérifier que l'application fonctionne correctement en visitant :
https://hameconnes.guillaume-rv.fr

## Dépannage

Si vous rencontrez des problèmes lors du déploiement :

1. Vérifiez les logs dans l'onglet "Actions" de GitHub
2. Assurez-vous que tous les secrets sont correctement configurés
3. Vérifiez les permissions des fichiers sur le serveur
4. Consultez les logs d'erreur sur le serveur (/home/hameconnes/www/storage/logs)

## Mise à jour de l'application

Pour mettre à jour l'application en production :

1. Poussez vos modifications sur la branche `main`
2. Le workflow GitHub Actions se déclenchera automatiquement
3. Vérifiez le statut du déploiement dans l'onglet "Actions"
