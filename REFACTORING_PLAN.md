# Plan de Refactorisation : Transformation en Application de Gestion de Tâches

## Vue d'ensemble
Transformer l'application Laravel d'un système de gestion de projet en une application autonome de gestion de tâches en supprimant toutes les fonctionnalités liées aux projets.

## Phase 1 : Préparation et Sauvegarde

### 1.1 Sauvegarde
- [ ] Créer une sauvegarde complète de la base de données
- [ ] Créer une branche Git pour la refactorisation
- [ ] Documenter l'état actuel

### 1.2 Analyse des Dépendances
- [ ] Identifier toutes les tâches existantes avec project_id
- [ ] Vérifier les contraintes de clés étrangères
- [ ] Analyser les données existantes

## Phase 2 : Migration de Base de Données

### 2.1 Suppression des Contraintes de Clés Étrangères
**Fichiers à créer :**
- `database/migrations/YYYY_MM_DD_remove_project_constraints.php`

**Actions :**
- [ ] Supprimer la contrainte FK `project_id` de la table `tasks`
- [ ] Supprimer la colonne `project_id` de la table `tasks`
- [ ] Supprimer la table `project_teams`
- [ ] Supprimer la table `projects`

### 2.2 Nettoyage des Relations Files
**Actions :**
- [ ] Vérifier si la table `files` a une relation avec `projects`
- [ ] Supprimer les contraintes si nécessaire

## Phase 3 : Modification des Modèles

### 3.1 Modèle Task
**Fichier :** `app/Models/Task.php`
**Actions :**
- [ ] Supprimer `'project_id'` du tableau `$fillable`
- [ ] Supprimer la méthode `project()`
- [ ] Conserver toutes les autres relations (user, reminders, checklistItems)

### 3.2 Modèle User
**Fichier :** `app/Models/User.php`
**Actions :**
- [ ] Supprimer la méthode `projects()`
- [ ] Supprimer la méthode `projectMembers()`
- [ ] Conserver toutes les autres relations

### 3.3 Suppression des Modèles Liés aux Projets
**Fichiers à supprimer :**
- [ ] `app/Models/Project.php`
- [ ] `app/Models/ProjectTeam.php`

## Phase 4 : Modification des Contrôleurs

### 4.1 TaskController
**Fichier :** `app/Http/Controllers/TaskController.php`
**Actions :**
- [ ] Modifier la méthode `index()` pour ne plus dépendre des projets
- [ ] Modifier la méthode `store()` pour supprimer le paramètre Project
- [ ] Supprimer toutes les références aux projets
- [ ] Créer une nouvelle route pour créer des tâches indépendantes

### 4.2 Suppression du ProjectController
**Fichier à supprimer :**
- [ ] `app/Http/Controllers/ProjectController.php`

### 4.3 Modification des Autres Contrôleurs
**Fichiers à vérifier :**
- [ ] `FileController.php` - Supprimer les références aux projets si présentes

## Phase 5 : Modification des Routes

### 5.1 Fichier web.php
**Fichier :** `routes/web.php`
**Actions :**
- [ ] Supprimer toutes les routes liées aux projets
- [ ] Modifier les routes des tâches pour qu'elles soient indépendantes
- [ ] Mettre à jour la route du dashboard
- [ ] Supprimer les imports des contrôleurs de projets

## Phase 6 : Modification des Vues

### 6.1 Layout Principal
**Fichier :** `resources/views/layouts/app.blade.php`
**Actions :**
- [ ] Supprimer le lien "Projets" de la navigation
- [ ] Mettre à jour les liens vers les tâches

### 6.2 Dashboard
**Fichier :** `resources/views/dashboard.blade.php`
**Actions :**
- [ ] Modifier le lien "Voir les tâches" pour pointer vers `tasks.index`
- [ ] Supprimer toute référence aux projets

### 6.3 Vues des Tâches
**Fichiers :** `resources/views/tasks/`
**Actions :**
- [ ] `index.blade.php` - Supprimer toute logique conditionnelle liée aux projets
- [ ] `create.blade.php` - Créer une vue pour créer des tâches indépendantes
- [ ] `show.blade.php` - Supprimer les références aux projets
- [ ] `edit.blade.php` - Supprimer les références aux projets

### 6.4 Suppression des Vues Projets
**Dossier à supprimer :**
- [ ] `resources/views/projects/`

## Phase 7 : Nettoyage et Tests

### 7.1 Nettoyage du Code
**Actions :**
- [ ] Rechercher et supprimer toutes les références restantes aux projets
- [ ] Vérifier les imports inutilisés
- [ ] Nettoyer les commentaires obsolètes

### 7.2 Tests et Validation
**Actions :**
- [ ] Tester la création de nouvelles tâches
- [ ] Tester l'affichage des tâches existantes
- [ ] Vérifier que toutes les autres fonctionnalités fonctionnent
- [ ] Tester les relations (reminders, checklist items)

### 7.3 Documentation
**Actions :**
- [ ] Mettre à jour le README.md
- [ ] Documenter les changements apportés
- [ ] Créer un guide de migration pour les utilisateurs

## Phase 8 : Optimisations (Optionnel)

### 8.1 Améliorations UX
**Actions :**
- [ ] Améliorer l'interface de création de tâches
- [ ] Ajouter des filtres et tri pour les tâches
- [ ] Optimiser l'affichage Kanban

### 8.2 Fonctionnalités Supplémentaires
**Actions :**
- [ ] Ajouter des catégories/tags pour les tâches
- [ ] Améliorer la recherche de tâches
- [ ] Ajouter des statistiques de productivité

## Risques et Considérations

### Risques Identifiés :
1. **Perte de données** : Les tâches existantes perdront leur association aux projets
2. **Références cassées** : Possibles liens cassés dans l'interface
3. **Fonctionnalités manquantes** : Certaines fonctionnalités de groupement pourraient manquer

### Mitigation :
1. **Sauvegarde complète** avant toute modification
2. **Tests exhaustifs** après chaque phase
3. **Migration progressive** avec validation à chaque étape

## Ordre d'Exécution Recommandé

1. **Phase 1** : Préparation (Critique)
2. **Phase 2** : Migration DB (Critique - Irréversible)
3. **Phase 3** : Modèles (Haute priorité)
4. **Phase 4** : Contrôleurs (Haute priorité)
5. **Phase 5** : Routes (Haute priorité)
6. **Phase 6** : Vues (Moyenne priorité)
7. **Phase 7** : Tests (Haute priorité)
8. **Phase 8** : Optimisations (Basse priorité)

## Estimation de Temps
- **Phases 1-7** : 4-6 heures de travail
- **Phase 8** : 2-4 heures supplémentaires (optionnel)

## Points de Validation
Après chaque phase majeure, vérifier :
- [ ] L'application démarre sans erreur
- [ ] Les tâches existantes sont toujours accessibles
- [ ] Les nouvelles tâches peuvent être créées
- [ ] Toutes les autres fonctionnalités (routines, notes, etc.) fonctionnent

## Détails Techniques Spécifiques

### Fichiers Identifiés à Modifier/Supprimer

#### Modèles à Supprimer :
- `app/Models/Project.php`
- `app/Models/ProjectTeam.php`

#### Modèles à Modifier :
- `app/Models/Task.php` : Supprimer project_id et relation project()
- `app/Models/User.php` : Supprimer relations projects() et projectMembers()
- `app/Models/File.php` : Vérifier s'il y a une relation avec Project (pas trouvée dans l'analyse)

#### Contrôleurs à Supprimer :
- `app/Http/Controllers/ProjectController.php`

#### Contrôleurs à Modifier :
- `app/Http/Controllers/TaskController.php` : Refactoriser complètement
- `app/Http/Controllers/FileController.php` : Supprimer type 'project' si présent

#### Migrations à Créer :
- Migration pour supprimer la contrainte FK project_id de tasks
- Migration pour supprimer la colonne project_id de tasks
- Migration pour supprimer la table project_teams
- Migration pour supprimer la table projects

#### Routes à Modifier :
Dans `routes/web.php` :
- Supprimer : `Route::resource('projects', ProjectController::class);`
- Supprimer : `Route::post('project/team', ...)`
- Supprimer : `Route::post('project/team/remove', ...)`
- Supprimer : `Route::get('projects/{project}/tasks', ...)`
- Supprimer : `Route::post('projects/{project}/tasks', ...)`
- Modifier : Routes tasks pour qu'elles soient indépendantes
- Ajouter : Route pour créer des tâches (GET et POST)

#### Vues à Supprimer :
- `resources/views/projects/` (dossier complet)

#### Vues à Modifier :
- `resources/views/layouts/app.blade.php` : Supprimer lien navigation "Projets"
- `resources/views/dashboard.blade.php` : Modifier lien "Voir les tâches"
- `resources/views/tasks/index.blade.php` : Supprimer logique conditionnelle projet
- `resources/views/tasks/show.blade.php` : Supprimer références projet
- `resources/views/tasks/edit.blade.php` : Supprimer références projet

#### Vues à Créer :
- `resources/views/tasks/create.blade.php` : Nouvelle vue pour créer des tâches

### Contraintes de Base de Données Identifiées

#### Table `tasks` :
- Contrainte FK : `tasks_project_id_foreign` vers `projects(id)`
- Colonne à supprimer : `project_id` (bigint unsigned NOT NULL)

#### Table `project_teams` :
- Contrainte FK : `project_teams_project_id_foreign` vers `projects(id)`
- Contrainte FK : `project_teams_user_id_foreign` vers `users(id)`
- Table complète à supprimer

#### Table `projects` :
- Contrainte FK : `projects_user_id_foreign` vers `users(id)`
- Table complète à supprimer

### Données Existantes à Considérer

Selon le fichier SQL fourni, il y a des tâches existantes avec project_id :
- Task ID 2 : project_id = 1
- Task ID 5 : project_id = 3
- Task ID 6 : project_id = 1

**Important** : Ces tâches perdront leur association aux projets mais resteront fonctionnelles.

### Commandes Laravel Utiles

```bash
# Créer les migrations
php artisan make:migration remove_project_dependencies_from_tasks
php artisan make:migration drop_project_teams_table
php artisan make:migration drop_projects_table

# Exécuter les migrations
php artisan migrate

# Vérifier l'état des migrations
php artisan migrate:status

# Rollback si nécessaire (avant suppression des tables)
php artisan migrate:rollback --step=3
```

### Tests de Validation Recommandés

1. **Test de création de tâche** :
   ```php
   // Vérifier qu'une tâche peut être créée sans project_id
   $task = Task::create([
       'user_id' => 1,
       'title' => 'Test Task',
       'priority' => 'medium'
   ]);
   ```

2. **Test d'affichage des tâches** :
   - Vérifier que toutes les tâches existantes s'affichent
   - Vérifier que le Kanban fonctionne sans projets

3. **Test des relations** :
   - Vérifier que les relations avec User, Reminders, ChecklistItems fonctionnent
   - Tester la création de rappels pour les tâches

### Ordre d'Exécution Détaillé

1. **Sauvegarde** : `mysqldump` ou export via phpMyAdmin
2. **Migration DB** : Exécuter dans l'ordre les 3 migrations
3. **Modèles** : Modifier Task.php et User.php, supprimer Project.php et ProjectTeam.php
4. **Contrôleurs** : Modifier TaskController.php, supprimer ProjectController.php
5. **Routes** : Nettoyer web.php
6. **Vues** : Modifier layout, dashboard, tasks/, supprimer projects/
7. **Tests** : Validation complète de toutes les fonctionnalités
