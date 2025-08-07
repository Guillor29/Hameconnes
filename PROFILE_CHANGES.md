# Changements pour le profil utilisateur et l'icône du site

## 1. Changement de l'icône du site

### Problème
L'icône du site affichait le logo Laravel par défaut au lieu de l'emoji hameçon (🎣).

### Solution
- Ajout d'une approche moderne pour l'icône du site en utilisant un SVG inline avec l'emoji hameçon
- Mise à jour du fichier `app.blade.php` pour inclure les balises favicon avec l'emoji

## 2. Implémentation du menu déroulant du profil utilisateur

### Fonctionnalités ajoutées
- Menu déroulant accessible en cliquant sur l'icône utilisateur dans la barre de navigation
- Affichage du nom et de l'email de l'utilisateur connecté
- Liens vers les pages de profil:
  - Mon profil
  - Changer mot de passe
  - Gestion des utilisateurs (pour les administrateurs uniquement)
  - Déconnexion
- Vérification du rôle administrateur pour afficher les options d'administration
- Mise à jour du menu mobile avec les mêmes fonctionnalités

## 3. Création des fonctionnalités de profil utilisateur

### Pages de profil
- **Affichage du profil**: Visualisation des informations de l'utilisateur
- **Modification du profil**: Formulaire pour mettre à jour le nom et l'email
- **Changement de mot de passe**: Formulaire sécurisé pour modifier le mot de passe

### Contrôleurs et routes
- Création du `UserProfileController` avec les méthodes:
  - `show()`: Affichage du profil
  - `edit()`: Formulaire d'édition du profil
  - `update()`: Mise à jour des informations
  - `editPassword()`: Formulaire de changement de mot de passe
  - `updatePassword()`: Mise à jour du mot de passe
- Ajout des routes correspondantes dans `web.php`

## 4. Gestion des utilisateurs pour les administrateurs

### Contrôle d'accès
- Vérification du rôle administrateur via le middleware `CheckRole`
- Création d'un endpoint API pour récupérer les informations de l'utilisateur connecté
- Mise à jour du composant `NavigationMenu.vue` pour vérifier si l'utilisateur est administrateur

### API pour les informations utilisateur
- Création du `UserController` avec la méthode `current()`
- Ajout d'une route API pour récupérer les informations de l'utilisateur connecté

## Comment tester les changements

1. **Icône du site**:
   - Vérifier que l'emoji hameçon (🎣) apparaît dans l'onglet du navigateur

2. **Menu déroulant du profil**:
   - Cliquer sur l'icône utilisateur dans la barre de navigation
   - Vérifier que le menu déroulant s'affiche avec les liens appropriés
   - Vérifier que le nom et l'email de l'utilisateur connecté sont affichés correctement

3. **Pages de profil**:
   - Accéder à la page "Mon profil" pour voir les informations
   - Tester la modification des informations du profil
   - Tester le changement de mot de passe

4. **Gestion des utilisateurs (administrateurs uniquement)**:
   - Se connecter en tant qu'administrateur (guillaume.rv29@gmail.com)
   - Vérifier que le lien "Gestion des utilisateurs" est visible dans le menu
   - Accéder à la page de gestion des utilisateurs
