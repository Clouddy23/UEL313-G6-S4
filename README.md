# UEL313-G6-S4

Projet Symfony 6.4 (LTS) en groupe : gestion de liens (CRUD) stockés en base (listing, ajout, édition, suppression) + Documentation complète (méthode, organisation, captures, ressources)

## Dépôt public

Le projet est hébergé sur Github : [https://github.com/Clouddy23/UEL313-G6-S4/](https://github.com/Clouddy23/UEL313-G6-S4/)

## Objectifs

- Réaliser une **installation Symfony 6.4 (LTS)**.
- Mettre en place des fonctionnalités proches du précédent projet “Watson” :
  - **Lister** les liens stockés en base de données
  - **Ajouter** un lien via formulaire (titre, URL, descriptif)
  - **Mettre à jour** un lien
  - **Supprimer** un lien

---

## Principe général de collaboration

### Membres du groupe

| Étudiant.e  |   Alias    | % participation |
| :---------: | :--------: | :-------------: |
| Mathilde C. | Clouddy23  |                 |
|   Kamo G.   | Spaghette5 |                 |
| Mathieu L.  |  mathleys  |                 |
| Filippos K. |  filkat34  |                 |

### Répartition du travail

| Activité | Responsable(s) | Branche |
|---|---|---|
| Installation Symfony + configuration environnement | Mathilde Chauvet | `main` |
| Création de la BDD, des entités et interfaces | Filippos K. | `feature/datastructure` |
| CRUD Link (Entity, Form, Controller, Twig) | Mathieu Leyssene | `feature/crud-link` |
| UI Twig/CSS (base) | Kamo Guillon | `feature/ui` |
| Back office + sécurité |          | `feature/backoffice` |
| Documentation + captures + PDF    | Filippos K. & Mathilde Chauvet | `feature/documentation` |


### Calendrier

Une réunion d'équipe est prévue à chaque fin d'échéance.

| Echéance | Objectif                                                                                                                                                                                                 |
| :------: | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|  15/12   | Phase d'installation et de réflexion : installation Symfony 6.4 LTS, choix techniques, préparation du reposity, choix techniques (BDD, UI...) et répartition des tâches et création des issues/branches. |
|  16/12   | Phase de développement : mise en place du modèle + début CRUD sur des branches distinctes.                                                                                                               |
|  17/12   | Phase de développement : CRUD complet, validations formulaire, messages utilisateur, UI                                                                                                                  |
|  18/12   | Relecture des branches (PR), corrections, fusion vers `main` et tests manuels fonctionnels (parcours CRUD)                                                                                               |
|  19/12   | Finalisation du rendu : captures écran, schémas, rédaction et export du PDF, ajout des ressources et % participation.                                                                                    |

## Développement du projet

### Mise à jour du fichier .env

- Modification du fichier _.env_ en renseignant l'URL correct de la base de données : `DATABASE_URL="sqlite:///%kernel.project_dir%/var/watson.db"`
- Modification du fichier _.gitignore_ pour exclure le fichier _watson.db_ afin qu'il puisse être partagé avec les collaborateurs.

### Modèle des données et initialisation de la base

Nous avons par la suite procédé à la construction de modèle de données :

- Nous avons d'abord créé les trois classes de l'application watson (User, Tag, Link) ainsi que leurs méthodes en prenant soin de bien traduire le lien d'association entre Link et Tag.
- Dans une perspective de standardisation et de clarté du code, nous avons créé des interfaces pour chacune de ces trois entités.
- Dans le terminal `composer dump-autoload` pour régénerer le fichier autoload de Composer et mettre à jour la liste des classes et fichiers de l'application.
- Dans le terminal `doctrine:schema:update --force` qui permet de créer la base de données _sqlite_ et synchroniser sa structure avec les entités que nous venons de créer.
- En utilisant le logiciel _DBBrowser_ nous avons exécuté des scripts SQL pour remplir la base avec des données pour les tests.

![DB Browser](/docs/dbbrowser.png)

### Installation de Nelmio

Pour faciliter les tests de l'API, nous avons installé Nelmio avec la commande :
`composer require nelmio/api-doc-bundle`

Pour le configurer, nous avons suivi la documentation disponible sur le site de [Symfony](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html#installation)

### Création du contrôleur pour les _Users_

Pour tester que la communication entre la base de données et l'application fonctionne, nous avons créé les différentes méthodes CRUD pour du contrôleur appelé par la route `/users`.

Pour que _Nelmio_ fonctionne, nous avons ajouté au dessus de chacune de ces méthodes l'attribut correspondant commençant par `#[OA\METHOD(...)]`

Nous avons ensuite procédé à des tests de fonctionnement des différentes routes de l'API grâce à l'UI de _Nelmio_ accessible sur `http://localhost:8000/api/doc`

![Nelmio](/docs/nelmio_users.png)

## Tests manuels fonctionnels

## Licence

Projet pédagogique — usage formation.
