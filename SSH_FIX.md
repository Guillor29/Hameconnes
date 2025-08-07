# Résolution du problème de connexion SSH dans GitHub Actions

## Problème

Lors de l'exécution du workflow GitHub Actions, l'étape "Rename .env.production to .env on server" échouait avec l'erreur suivante :

```
2025/08/07 16:25:07 dial tcp ***:22: i/o timeout
Error: Process completed with exit code 1.
```

Cette erreur indique que la connexion SSH au serveur O2switch n'a pas pu être établie, probablement en raison de restrictions d'accès SSH sur l'hébergement.

## Analyse

Le workflow utilisait l'action `appleboy/ssh-action@master` pour se connecter au serveur via SSH et exécuter les commandes suivantes :
- Renommer le fichier .env.production en .env
- Exécuter les commandes artisan pour mettre en cache les configurations et les routes
- Exécuter les migrations de la base de données

L'erreur de timeout suggère que :
1. Le port SSH (22) est bloqué par un pare-feu
2. Le service SSH n'est pas activé sur le serveur
3. L'hébergeur O2switch ne permet pas les connexions SSH entrantes

## Solution

Pour résoudre ce problème, nous avons remplacé l'approche SSH par une solution basée sur HTTP et FTP :

1. **Création d'un script de déploiement PHP** (`public/deploy.php`) qui effectue les mêmes opérations que les commandes SSH :
   - Renommer le fichier .env.production en .env
   - Exécuter les commandes artisan pour la mise en cache
   - Exécuter les migrations de la base de données

2. **Sécurisation du script** avec un token de déploiement généré dynamiquement pour chaque exécution du workflow.

3. **Modification du workflow GitHub Actions** pour :
   - Générer un token de déploiement aléatoire
   - Ajouter ce token au fichier .env.production
   - Déployer tous les fichiers (y compris le script de déploiement) via FTP
   - Appeler le script de déploiement via une requête HTTP POST avec le token

## Détails techniques

### Script de déploiement PHP

Le script `public/deploy.php` :
- Vérifie que la requête est de type POST
- Valide le token de déploiement fourni
- Exécute les opérations de post-déploiement
- Journalise toutes les actions pour faciliter le débogage
- Retourne une réponse JSON avec le statut de l'opération

### Modifications du workflow GitHub Actions

Dans le fichier `.github/workflows/deploy.yml`, nous avons :
1. Supprimé l'étape SSH qui échouait
2. Ajouté une étape pour générer un token de déploiement aléatoire
3. Ajouté une étape pour inclure ce token dans le fichier .env.production
4. Ajouté une étape pour appeler le script de déploiement via HTTP

### Mise à jour de la documentation

Les fichiers de documentation ont été mis à jour pour refléter cette nouvelle approche :
- `DEPLOYMENT.md` : Mise à jour des prérequis et du processus de déploiement
- `GITHUB_ACTIONS_SUMMARY.md` : Mise à jour des fonctionnalités du workflow

## Avantages de cette solution

1. **Contournement des restrictions SSH** : Fonctionne même si l'accès SSH est limité ou désactivé
2. **Sécurité** : Utilisation d'un token unique pour chaque déploiement
3. **Traçabilité** : Journalisation des actions pour faciliter le débogage
4. **Flexibilité** : Possibilité d'étendre facilement le script pour d'autres tâches de post-déploiement

## Comment tester

1. Déclenchez manuellement le workflow GitHub Actions
2. Vérifiez que le déploiement se termine avec succès
3. Vérifiez les logs du script de déploiement dans `storage/logs/deployment-*.log` sur le serveur

## Conclusion

Cette solution permet de contourner les limitations d'accès SSH sur l'hébergement O2switch tout en maintenant un processus de déploiement automatisé et sécurisé. Elle utilise des technologies standard (HTTP et FTP) qui sont généralement disponibles sur tous les hébergements web.
