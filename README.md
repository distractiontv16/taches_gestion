# Task Manager - Application de Gestion de Tâches

## Description du Projet

**Task Manager** est une application web développée avec Laravel permettant de gérer efficacement des tâches personnelles. Elle offre une interface intuitive de type Kanban (similaire à ClickUp ou Trello) pour organiser et suivre votre travail quotidien.

## Fonctionnalités Principales

- **Gestion complète des tâches** avec différents statuts (À faire, En cours, Terminé)
- **Interface Kanban** avec glisser-déposer pour réorganiser les tâches
- **Système de priorités** (Faible, Moyenne, Haute) pour organiser vos tâches
- **Notes personnelles** pour garder vos idées et informations importantes
- **Rappels et notifications par email** pour les tâches en retard
- **Tâches routinières** (journalières, hebdomadaires, mensuelles)
- **Gestion de fichiers** (upload et attachement aux tâches)
- **Authentification complète** avec vérification par email
- **Réinitialisation de mot de passe** par email
- **Interface responsive** adaptée aux mobiles et tablettes

## Structure du Projet

```
Task-Manager/
├── app/                   # Logique principale de l'application
│   ├── Console/          # Commandes artisan et tâches planifiées
│   ├── Http/             # Contrôleurs, Middleware, Requests
│   ├── Mail/             # Classes pour l'envoi d'emails
│   ├── Models/           # Modèles Eloquent (User, Task, Project, etc.)
│   ├── Notifications/    # Notifications (email, etc.)
│   └── Providers/        # Fournisseurs de services
├── bootstrap/            # Fichiers d'amorçage de l'application
├── config/               # Fichiers de configuration
├── database/             # Migrations et seeders
│   ├── migrations/       # Définitions de structure de base de données
│   ├── factories/        # Factories pour les tests
│   └── seeders/         # Données initiales
├── public/               # Point d'entrée public et assets
├── resources/            # Vues, assets non-compilés, localisations
│   ├── js/              # Fichiers JavaScript
│   ├── css/             # Fichiers CSS
│   └── views/           # Templates Blade
├── routes/               # Définitions des routes
│   ├── web.php          # Routes web
│   └── console.php      # Routes console
├── storage/              # Fichiers uploadés, logs, cache
└── tests/                # Tests automatisés
```

## Fichiers Clés

- **app/Models/User.php** - Modèle utilisateur avec relations vers tâches, notes, etc.
- **app/Models/Task.php** - Définition des tâches avec leurs relations et attributs
- **app/Models/Note.php** - Définition des notes personnelles
- **app/Models/Routine.php** - Définition des tâches routinières
- **app/Console/Commands/SendReminderEmails.php** - Commande d'envoi des emails de rappel
- **routes/web.php** - Définition de toutes les routes de l'application
- **.env** - Configuration de l'environnement (base de données, mail, etc.)

## Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL (ou MariaDB)
- Serveur web (Apache, Nginx, ou le serveur intégré de PHP)
- Node.js et NPM (pour les assets frontend)

## Installation

### 1. Cloner le Projet

```bash
git clone https://github.com/AresGn/taskmanager.git
cd Task-Manager
```

### 2. Installer les Dépendances

```bash
composer install
npm install
npm run build
```

### 3. Configuration de l'Environnement

```bash
cp .env.example .env
php artisan key:generate
```

Éditez le fichier `.env` pour configurer:
- La connexion à la base de données
- Les paramètres de mail (pour les notifications)
- Le fuseau horaire (recommandé: `APP_TIMEZONE=Africa/Porto-Novo` pour le Bénin)

### 4. Création de la Base de Données

Créez une base de données MySQL vide avec le nom spécifié dans votre fichier `.env`

### 5. Exécution des Migrations et Seeders

```bash
php artisan migrate --seed
```

### 6. Démarrage du Serveur de Développement

```bash
php artisan serve
```

Accédez à l'application dans votre navigateur: http://localhost:8000

## Importation de la Base de Données (Alternative à la Migration)

Si vous préférez utiliser un dump SQL au lieu des migrations:

1. Créez une base de données vide dans MySQL 
2. Importez le fichier SQL via phpMyAdmin:
   - Accédez à phpMyAdmin
   - Sélectionnez votre base de données
   - Cliquez sur l'onglet "Importer"
   - Sélectionnez le fichier SQL à importer
   - Cliquez sur "Exécuter"

3. Alternative en ligne de commande:
```bash
mysql -u votre_utilisateur -p votre_base_de_donnees < chemin/vers/dump.sql
```

## Test des Emails en Développement

L'application est configurée pour utiliser Mailtrap en environnement de développement.

1. Créez un compte sur [Mailtrap](https://mailtrap.io/)
2. Obtenez vos identifiants SMTP depuis Mailtrap
3. Configurez-les dans votre fichier `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username_mailtrap
MAIL_PASSWORD=votre_password_mailtrap
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=taskmanager@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Configuration des Emails en Production

Pour l'environnement de production, vous pouvez utiliser un service SMTP comme:
- SendGrid
- Mailgun
- Amazon SES
- Gmail (limité en volume)

Exemple avec SendGrid:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=votre_cle_api_sendgrid
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=votre_email_verifie@domaine.com
MAIL_FROM_NAME="${APP_NAME}"
```

## Tester les Rappels par Email

### En Développement

1. Créez une tâche avec une date d'échéance dépassée
2. Exécutez la commande:
```bash
php artisan app:send-reminder-emails
```
3. Vérifiez votre boîte de réception Mailtrap

### Automatisation des Rappels

En production, configurez un cron job pour exécuter la commande toutes les heures:

```
0 * * * * cd /chemin/vers/votre/projet && php artisan app:send-reminder-emails >> /dev/null 2>&1
```

En développement, simulez le scheduler avec:
```bash
php artisan schedule:work
```

## Utilisation de l'Application

### 1. Inscription et Connexion

- Inscrivez-vous avec votre adresse email et mot de passe
- Vérifiez votre email (vérification requise pour accéder à l'application)
- Connectez-vous avec vos identifiants

### 2. Gestion des Tâches

- Créez des tâches depuis le dashboard ou la page dédiée
- Définissez le titre, la description, la priorité et la date d'échéance
- Organisez vos tâches par statut : À faire, En cours, Terminé
- Utilisez le glisser-déposer pour changer le statut des tâches
- Modifiez ou supprimez vos tâches selon vos besoins

### 3. Utilisation des Rappels

- Les rappels sont automatiquement créés pour les tâches avec une date d'échéance
- Recevez une notification par email 2 heures avant l'échéance
- Gérez vos rappels depuis la section dédiée

### 4. Tâches Routinières

- Créez des tâches qui se répètent (quotidien, hebdomadaire, mensuel)
- Suivez les récurrences dans la section "Routines"
- Configurez les jours ouvrables uniquement si nécessaire

### 5. Notes Personnelles

- Créez des notes pour garder vos idées et informations importantes
- Organisez vos notes par date et heure
- Accédez rapidement à vos notes depuis le dashboard

## Comment Ajouter une Tâche via SQL

Vous pouvez insérer directement une tâche dans la base de données avec cette requête SQL:

```sql
INSERT INTO tasks (
    user_id,
    title,
    description,
    due_date,
    priority,
    status,
    created_at,
    updated_at
)
VALUES (
    1, -- ID de l'utilisateur
    'Nouvelle tâche',
    'Description de la tâche',
    '2025-06-10 14:00:00', -- Date d'échéance
    'high', -- Priorité ('low', 'medium', 'high')
    'to_do', -- Statut ('to_do', 'in_progress', 'completed')
    NOW(), -- Date de création
    NOW() -- Date de mise à jour
);
```

## Dépannage

### Problèmes d'Emails

- Vérifiez vos paramètres SMTP dans le fichier `.env`
- Assurez-vous que votre service d'emails (Mailtrap en développement) est actif
- Vérifiez les logs dans `storage/logs/laravel.log`

### Problèmes de Base de Données

- Vérifiez les paramètres de connexion dans `.env`
- Assurez-vous que votre base de données existe
- Vérifiez les permissions de l'utilisateur MySQL

### Problèmes de Fuseaux Horaires

Si les rappels ne fonctionnent pas correctement:
- Vérifiez le fuseau horaire dans `config/app.php`
- Assurez-vous qu'il correspond à votre région

## Contribuer au Projet

Les contributions sont les bienvenues! Pour contribuer:

1. Forkez le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b nouvelle-fonctionnalite`)
3. Committez vos changements (`git commit -m 'Ajout d'une nouvelle fonctionnalité'`)
4. Poussez vers la branche (`git push origin nouvelle-fonctionnalite`)
5. Ouvrez une Pull Request

## Licence

Ce projet est sous licence MIT - voir le fichier LICENSE pour plus de détails.

## Contact

Pour toute question ou suggestion, veuillez ouvrir une issue sur GitHub.


php artisan key:generate
npm install
php artisan serve