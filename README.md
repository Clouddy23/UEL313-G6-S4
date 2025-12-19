# UEL313-G6-S4

**Projet universitaire Symfony 6.4 (LTS) en groupe** : application de gestion de liens stockés en base de données (CRUD : listing, ajout, modification, suppression).

**Dépôt public** : le projet est hébergé sur Github [https://github.com/Clouddy23/UEL313-G6-S4/](https://github.com/Clouddy23/UEL313-G6-S4/)

## Objectifs

- Mettre en place une application Symfony **6.4 (LTS)**
- Développer une gestion de liens stockés en base de données :
  - **Lister** les liens
  - **Ajouter** un lien via formulaire (**titre**, **URL**, **descriptif**)
  - **Mettre à jour** un lien
  - **Supprimer** un lien
- Approcher davantage le projet “Watson” en ajoutant :
  - Des **mots-clés** associés aux liens
  - Un **back office** sécurisé
  - Une **gestion d’utilisateurs**
  - Un rendu **Twig/CSS** de base

## Principe général de collaboration

### Membres du groupe

Tous les membres du groupe ont contribué de manière équilibrée et proportionnelle au projet.

| Étudiant.e  |   Alias    |
| :---------: | :--------: |
| Mathilde C. | Clouddy23  |
|   Kamo G.   | Spaghette5 |
| Mathieu L.  |  mathleys  |
| Filippos K. |  filkat34  |

### Répartition du travail

| Activité                                                    | Responsable(s)                                   | Branche                                            |
| ----------------------------------------------------------- | ------------------------------------------------ | -------------------------------------------------- |
| Base projet (Symfony 6.4 LTS) + configuration environnement | Mathilde Chauvet (Clouddy23)                     | `main`                                             |
| Modèle de données (BDD + entités + migrations)              | Filippos K. (filkat34)                           | `feature/datastructure`                            |
| CRUD Link (Entity, Form, Controller, Twig)                  | Mathieu L. (mathleys)                            | `feature/link controller` `feature/tag controller` |
| UI Twig/CSS (base)                                          | Kamo G. (Spaghette5)                             | `feature/ui`                                       |
| (Option) Back office + sécurité                             | Mathieu L. (mathleys)                            | `feature/backoffice`                               |
| Documentation + captures + PDF (README → PDF)               | Filippos K. (filkat34) & Mathilde C. (Clouddy23) | /                                                  |

### Calendrier de suivi du projet

Une réunion visio d'équipe est prévue à chaque fin d'échéance.

| Échéance | Objectif                                                                                          |
| :------: | :------------------------------------------------------------------------------------------------ |
|  15/12   | Phase d’installation : installation Symfony 6.4 LTS, préparation du repository.                   |
|  16/12   | Visio d'organisation : répartition des tâches, création des issues/branches.                      |
|  17/12   | Phase de développement : ???.                                                                     |
|  18/12   | Phase de relecture : Review et correction des branches (PR).                                      |
|  19/12   | Fin du projet : Tests manuels fonctionnels, fusion des branches vers `main`, finalisation du PDF. |

---

## Mise en place de l'environnement de développement

Tous les outils suivants doivent être disponibles “globalement” dans le terminal depuis n’importe quel dossier :

- PHP
- Composer
- Symfony CLI
- SQLite
- DB Browser

### Installation de Symfony CLI 6.4 LTS (MacOS + Terminal)

#### Installation et vérification de la version de Homebrew (gestionnaire de paquets)**

`/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
brew --version
brew --version`

#### Installation et vérification de la version PHP**

`brew update
brew install php
php -v`

#### Vérification des extensions PHP requises (ZIP, SQLite, PDO_SQLITE)**

`php -m | grep -E "zip|sqlite|pdo_sqlite"`

Le cas échéant, procéder à l'activation des extensions (suppression ";") :
`php --ini
extension=zip
extension=pdo_sqlite
extension=sqlite3`

#### Installation, mise à jour et vérification de la version de Composer (gestionnaire de dépendances PHP)**

`brew install composer
sudo composer self-update
composer -V`

#### Installation et vérification de Symfony CLI et de ses prérequis**

`brew install symfony-cli/tap/symfony-cli
symfony -V
symfony check:requirements`

#### Installation DB Browser pour SQLite (outil de visualisation BDD)**

`brew install --cask db-browser-for-sqlite`

### Création du projet Symfony 6.4 LTS (Watson-Symfony)

#### Se placer dans le dossier de travail (emplacement du projet)**

Création d’un nouveau projet Symfony dans un dossier `watson-symfony` en forçant la version 6.4 LTS :
`symfony new watson-symfony --version="6.4.*" --webapp
cd watson-symfony`

#### Configuration de SQLite dans .env.local**

On copie .env vers .env.local afin de : préserver la configuration par défaut (.env) et permettre à chaque membre du groupe d’avoir sa configuration locale (.env.local) :
`cp .env .env.local
code .env.local`

Dans .env.local, activer DATABASE_URL="SQLite..." (supprimer #) et désactiver DATABASE_URL="postgesql..." (ajouter #).

#### Installation de SQLite Browser**

Avec SQLite, la BDD est un fichier .db qui sera créé lors des migrations (après création des entités).
La visualisation de la BDD peut se faire grâce à l'installation de DB Browser :
`brew install db-browser-for-sqlite`

#### Test du lancement du serveur Symfony**

`cd "./S4/watson-symfony"
symfony server:start`

Se rendre à l'URL indiquée par le serveur : <http://127.0.0.1:8000>

![Home page Symfony](assets/screenshots/home-page-symfony.png)

### Mise à jour du fichier .env

- Modification du fichier _.env_ en renseignant l'URL correct de la base de données : `DATABASE_URL="sqlite:///%kernel.project_dir%/var/watson.db"`
- Modification du fichier _.gitignore_ pour exclure le fichier _watson.db_ afin qu'il puisse être partagé avec les collaborateurs.

## Base et manipulation de données

### Le Modèle

Nous avons d'abord recréé les trois entités de l'application watson (User, Tag, Link) ainsi que leurs méthodes de gestion de base. Il a fallu refactoriser l'entité _User_ d'origine et toutes ses références par la suite afin qu'elle implemente l'_UserInterface_ de Symfony, nécessaire pour utiliser le système d'authentification du framework.

Dans une perspective de standardisation et de clarté du code, nous avons créé des interfaces pour chacune de ces trois entités à l'exception de _User_ qui en possédait déjà une.

Deux _Repositories_ ont été créés pour y placer les requêtes complexes à la base de données, impliquant des jointures.

Nous avons pour finir créé plusieurs _Commandes_ pour trois commandes pour peupler la base de données.

### Création et mise en place de la base

- `php bin/console doctrine:schema:create` pour créer la base de données _SQLite_ "watson.db"
- `php bin/console app:create-admin-user` pour créer le premier adminitrateur de la base de données ;
- `php bin/console app:populate-tags` pour peupler la table des tags ;
- `php bin/console app:populate-links` pour peupler la table des liens ;
- `php bin/console app:populate-link-tag-associations` pour peupler la table des associations entre link et tags.

Suites à ces commandes nous vérifions grâce à _DB Browser_ que la base de données a été correctement remplie.

![DB Browser](/docs/dbbrowser.png)

### Documentation et tests CRUD

Pour faciliter la documentation, le codage et l'implementation des méthodes CRUD, nous avons installé _Nelmio_ avec la commande : `composer require nelmio/api-doc-bundle`

Pour le configurer, nous avons suivi la documentation disponible sur le site de [Symfony](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html#installation).

## Codage de l'API

Nous avons par la suite procédé au codage des différentes composantes de l'API pour et notamment des contrôleurs qui gèrent les différentes requêtes HTTP pour interroger la base des données.

Afin que chacune de ces requêtes soit documentée sur _Nelmio_, nous avons ajouté au dessus de chacune d'elles l'attribut correspondant commençant par `#[OA\METHOD(...)]`

### Contrôleur _Users_

Le contrôleur qui interroge la table des utilisateurs est exposé à la route `/api/users`. Quatre requêtes ont été codées.

![NelmioUsers](/docs/nelmio_users.png)

### Contrôleur _Liens_

Le contrôleur qui interroge la table des liens est exposé à la route `/api/links`. Neuf requêtes ont été codées.

![Nelmio routes links](/docs/nelmio_links.png)

### Contrôleur _Tags_

Le contrôleur qui interroge la table des mots clés (tags) est exposé à la route `/api/tags`. Huit requêtes ont été codées. Nous avons également créé les différentes méthodes CRUD pour implementer le lien d'association entre les liens et les mots clés.

![Nelmio routes tags](/docs/nelmio_tags.png)

Plus précisément, nous avons modélisé la relation ManyToMany entre Link et Tag :

- Link possède une collection de Tag ($tags)
- Tag possède une collection de Link ($links)
- Une table link_tag gère les associations en BDD

Pour éviter des incohérences côté application, nous avons synchronisé les deux côtés de la relation dans les méthodes :

- Link::addTag() / Link::removeTag()
- Tag::addLink() / Tag::removeLink()

Nous avons, enfin, développé un contrôleur TagController (API JSON) exposant des routes :

- GET /tags : liste de tous les tags
- POST /tags : création d’un tag (JSON { "name": "..." })
- GET /tags/{id} : détail d’un tag
- PUT /tags/{id} : modification d’un tag
- DELETE /tags/{id} : suppression d’un tag

A cela nous avons ajouté des routes permettant de gérer la relation tag et lien :

- POST /links/{linkId}/tags/{tagId} : associer un tag existant à un lien
- DELETE /links/{linkId}/tags/{tagId} : dissocier un tag d’un lien
- GET /links/{linkId}/tags : lister les tags d’un lien

Il est à noter que nous avons également réalisé une protection contre la création de doublons :

- Lors de la création d’un tag (`POST /tags`) on vérifie qu’un tag du même nom n’existe pas déjà en base
- Lors de l’association d’un tag à un lien (`POST /links/{linkId}/tags/{tagId}`) une vérification empêche d’ajouter 2 fois le même tag au même lien.

### Tests manuels fonctionnels

L'interface de _Nelmio_, nous a servi pour tester l'API et nous assurer que toutes les routes pointent vers des requêtes valides. Ci-dessous, un exemple de la requête GET sur la route _/api/links_ :

![Nelmio exemple test](/docs/test_getlinks.png)

## Implémentation des routes Web

Après avoir testé le bon fonctionnement de toutes les routes de l'API, nous avons implémenté les deux contrôleurs qui serviront à afficher les pages de l'application :

- Nous avons repris l'ancient contrôleur déjà implémenté du flux RSS et l'avons exposé à l'URL : `/feed` ;
- Nous avons également créé le contrôleur principal de l'application _HomeController_ qui reprend certaines méthodes testées lors de l'implémentation de l'API et retourne la page d'accueil ainsi que le backoffice.

## Authentification et inscription

Nous avons mis en place un système d'authentification et d'inscription en suivant la documentation de [Symfony](https://symfony.com/doc/current/security.html)

- `composer require symfony/security-bundle`pour installer le _SecurityBundle_ qui ajoute toutes les fonctionnalités nécessaires à l'authentification ;
- `php bin/console make:user` pour récréer une entité _User_ compatible avec Symfony. Nous avons dû procéder à des refactorisations et à la réinitialisation de la base de données;
- `php bin/console make:registration-form` pour ajouter un formulaire d'inscription et les méthodes nécessaires pour l'enregistrement dun nouvel utilisateur
- interventions au niveau du fichier `config/packages/security.yaml` pour sécuriser les routes par l'authentification

## Front-end

???

### Webographie

- Installation de PHP : [https://www.php.net/downloads.php](https://www.php.net/downloads.php)
- Installation de Composer : [https://getcomposer.org/](https://getcomposer.org/)
- Installation de Symfony CLI : [https://symfony.com/download](https://symfony.com/download)
- Installation de DB Browser pour SQLite : [https://sqlitebrowser.org/](https://sqlitebrowser.org/)
- Calendrier des releases Symfony : [https://symfony.com/releases](https://symfony.com/releases)
- Package - Symfony Demo : [https://packagist.org/packages/symfony/demo](https://packagist.org/packages/symfony/demo)
- Démarrage de Symfony : [https://symfony.com/doc/current/setup.html](https://symfony.com/doc/current/setup.)
- Installation de Nelmio : [https://symfony.com/bundles/NelmioApiDocBundle/current/index.html#installation](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html#installation)
