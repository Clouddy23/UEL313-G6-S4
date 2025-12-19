# UEL313-G6-S4 : projet de groupe Symfony

_WATSON_ est une application de gestion et de mutualisation de liens. Son code source est disponible sur [https://github.com/Clouddy23/UEL313-G6-S4/](https://github.com/Clouddy23/UEL313-G6-S4/)

## Objectifs

- [x] Prise en main du cadriciel Symfony 6.4 (LTS)
- [x] Développement d'une API
- [x] Création de templates TWIG
- [x] Fonctionnalités MVP
  - [x] listing de liens stockés en base de données ;
  - [x] ajout de lien en base de donnés à partir d'un formulaire, un lien étant composé au minimum d'un titre et d'une URL et d'un descriptif ;
  - [x] mise à jour des liens stockés en base de données
  - [x] suppression de liens en base de données
- [x] Fonctionnalités supplémentaires
  - [x] proposer une gestion de mots clés à associer à un lien;
  - [x] permettre un espace "back office" avec accès restreint pour gérer les liens;
  - [x] permettre la gestion d'utilisateurs pour le "back office";
  - [x] proposer un rendu Twig/CSS de base.

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

| Activité                                                    | Responsable(s)       |
| ----------------------------------------------------------- | -------------------- |
| Initialisation et configuration de l'environnement dev      | Mathilde             |
| Modèle et manipulation des données                          | Filippos             |
| API Users, authentification, inscription                    | Filippos             |
| API Liens                                                   | Mathieu              |
| API Tags                                                    | Mathilde             |
| UI Twig/CSS                                                 | Kamo                 |
| Documentation + captures + PDF (README → PDF)               | Groupe 6             |

### Calendrier de suivi du projet

| Échéance | Objectif                                                                                          |
| :------: | :------------------------------------------------------------------------------------------------ |
|  15/12   | Phase d’installation : installation Symfony 6.4 LTS, préparation du repository.                   |
|  16/12   | Visio d'organisation : répartition des tâches, création des issues/branches.                      |
|  17/12   | Phase de développement                                                                            |
|  18/12   | Phase de relecture : Review et correction des branches (PR).                                      |
|  20/12   | Fin du projet : Tests manuels fonctionnels, fusion des branches vers `main`, finalisation du PDF. |

## Mise en place de l'environnement de développement

Tous les outils suivants doivent être disponibles “globalement” dans le terminal depuis n’importe quel dossier :

- PHP
- Composer
- Symfony CLI
- SQLite
- DB Browser

### Installation de Symfony CLI 6.4 LTS (MacOS + Terminal)

#### Installation et vérification de la version de Homebrew (gestionnaire de paquets)

`/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
brew --version
brew --version`

#### Installation et vérification de la version PHP

`brew update
brew install php
php -v`

#### Vérification des extensions PHP requises (ZIP, SQLite, PDO_SQLITE)

`php -m | grep -E "zip|sqlite|pdo_sqlite"`

Le cas échéant, procéder à l'activation des extensions (suppression ";") :
`php --ini
extension=zip
extension=pdo_sqlite
extension=sqlite3`

#### Installation, mise à jour et vérification de la version de Composer (gestionnaire de dépendances PHP)

`brew install composer
sudo composer self-update
composer -V`

#### Installation et vérification de Symfony CLI et de ses prérequis

`brew install symfony-cli/tap/symfony-cli
symfony -V
symfony check:requirements`

#### Installation DB Browser pour SQLite

`brew install --cask db-browser-for-sqlite`

### Création du projet Watson-Symfony

#### Se placer dans le dossier de travail

Création d’un nouveau projet Symfony dans un dossier `watson-symfony` en forçant la version 6.4 LTS :
`symfony new watson-symfony --version="6.4.*" --webapp
cd watson-symfony`

#### Configurer de SQLite dans .env.local

On copie .env vers .env.local afin de : préserver la configuration par défaut (.env) et permettre à chaque membre du groupe d’avoir sa configuration locale (.env.local) :
`cp .env .env.local
code .env.local`

Dans .env.local, activer DATABASE_URL="SQLite..." (supprimer #) et désactiver DATABASE_URL="postgesql..." (ajouter #).

#### Installer de SQLite Browser

Avec SQLite, la BDD est un fichier .db qui sera créé lors des migrations (après création des entités).
La visualisation de la BDD peut se faire grâce à l'installation de DB Browser :
`brew install db-browser-for-sqlite`

#### Tester le serveur Symfony

`cd "./S4/watson-symfony"
symfony server:start`

Se rendre à l'URL indiquée par le serveur : <http://127.0.0.1:8000>

![Home page Symfony](docs/home-page-symfony.png)

### Mettre à jour du fichier .env

- Modification du fichier _.env_ en renseignant l'URL correct de la base de données : `DATABASE_URL="sqlite:///%kernel.project_dir%/var/watson.db"`
- Modification du fichier _.gitignore_ pour exclure le fichier _watson.db_ afin qu'il puisse être partagé avec les collaborateurs.

## Base de données

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

### Documentation et tests : _Nelmio_

Pour faciliter la documentation, le codage et l'implementation des méthodes CRUD, nous avons installé _Nelmio_ avec la commande : `composer require nelmio/api-doc-bundle`

Pour le configurer, nous avons suivi la documentation disponible sur le site de [Symfony](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html#installation).

L'interface de test est disponible sur la route [/api/doc](http://localhost:8000/api/doc).

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

La création de cette API a été ambitieuse et seulement une petite partie de ses méthodes a été reprise pour l'implémentation des routes publiques. Cependant elle pourra éventuellement servir dans la maintenance évolutive de l'application, si elle est appelée à évoluer.

## Architectures : SSR vs API REST

Dans son état actuel notre projet a une architecture hybride. Il a commencé, à des fins de test, comme un projet API REST comme on pourrait en trouver dans la plupart des frameworks _nodeJS_ : on a créé des endpoints API (dans LinkController, UserController, TagsController) accessibles par des requêtes HTTP et qui ne srvent qu'à la transmission pure d'informations entre le serveur et des clients sous format JSON.

![API Diagram](/docs/apidiagram.png)

Toutefois, la partie publique et "fonctionnelle" de l'application suit le modèle SSR (Server Side Rendering) telle qu'implémenté dans le contrôleur _HomeController_ : le serveur Symfony génère entièrement les pages HTML avant de les envoyer au navigateur. Plus précisément, ce contrôleur reçoit les requêtes HTTP, traite les données, puis utilise Twig pour produire le HTML final avant de l'envoyer au navigateur de l'utilisateur.

![SSR Diagram](/docs/ssrdiagram.png)

## Implémentation des routes Web

Après avoir testé le bon fonctionnement de toutes les routes de l'API, nous avons implémenté les deux contrôleurs qui serviront à afficher les pages de l'application :

- Nous avons repris l'ancient contrôleur déjà implémenté du flux RSS et l'avons exposé à l'URL : `/feed` ;
- Nous avons également créé le contrôleur principal de l'application _HomeController_ qui reprend certaines méthodes testées lors de l'implémentation de l'API et retourne la page d'accueil ainsi que le backoffice.

## Authentification et inscription

Nous avons mis en place un système d'authentification et d'inscription en suivant la documentation de [Symfony](https://symfony.com/doc/current/security.html)

- `composer require symfony/security-bundle`pour installer le _SecurityBundle_ qui ajoute toutes les fonctionnalités nécessaires à l'authentification ;
- `php bin/console make:user` pour récréer une entité _User_ compatible avec Symfony. Nous avons dû procéder à des refactorisations et à la réinitialisation de la base de données;
- `php bin/console make:registration-form` pour ajouter un formulaire d'inscription et les méthodes nécessaires pour l'enregistrement dun nouvel utilisateur
- interventions au niveau du fichier `config/packages/security.yaml` pour sécuriser les routes en exigeant une authentification en cas de requêtes sur les routes de l'APIet en donnant un accès public aux routes de `/login`, `register` et `feed`

![firewallApi](/docs/protecapiroutes.png)

![PublicRoutes](/docs/publicroutes.png)

- inverventions également à l'intérieur de la route du _backoffice_ pour discriminer, concernant la disponibilité des deux onglets (utilisateurs, liens), en fonction des rôles des utilisateurs connectés : le rôle USER ne peut que ajouter/modifier/supprimer des liens alors que le rôle ADMIN peut en plus gérer les comptes des utilisateurs.

![TagsBackofficePermissions](/docs/backofficetabs.png)

## Front-end

### Templates TWIG

Les templates `base.html.twig`, `login.html.twig` et `register.html.twig` ont été générés automatiquement par _Symfony_ pour le premier lors de la création du projet et, pour les deux autres, lors de l'installation du _SecurityBundle_.

Le template `base.html.twig` comporte la mise en page générale du site avec la navbar et le footer : tous les autres templates en dépendent.

Nous avons créer deux templates supplémentaires:

- `index.html.twig` qui constitue la page d'accueil du site permettant de visualiser l'ensemble des liens et offrant un champ de recherche ;

- `templates\backoffice.html.twig` qui constitue la page du backoffice fonctionnant grâce à deux modales (et le script javascript nécessaire pour leur gestion) pour l'affichage et la gestion des utilisateurs et des liens.

### Stylage et responsivité

### Webographie

- [PHP](https://www.php.net/downloads.php)
- [Composer](https://getcomposer.org/)
- [Symfony CLI](https://symfony.com/download)
- [Symfony SecurityBundle](https://symfony.com/doc/current/security)
- [Mise en place d'un projet Symfony](https://symfony.com/doc/current/setup)
- [DB Browser](https://sqlitebrowser.org/)
- [Nelmio](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html#installation)
