# UEL313-G6-S4
**Projet universitaire Symfony 6.4 (LTS) en groupe** : application de gestion de liens stockés en base de données (CRUD : listing, ajout, modification, suppression).

**Dépôt public** : le projet est hébergé sur Github [https://github.com/Clouddy23/UEL313-G6-S4/](https://github.com/Clouddy23/UEL313-G6-S4/)

**Licence** : projet pédagogique — usage formation.

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

| Étudiant.e  | Alias     |
|:----------:|:----------:|
| Mathilde C.| Clouddy23  |
| Kamo G.    | Spaghette5 |
| Mathieu L. | mathleys   |
| Filippos K.| filkat34   |

### Répartition du travail

| Activité | Responsable(s) | Branche |
|---|---|---|
| Base projet (Symfony 6.4 LTS) + configuration environnement | Mathilde Chauvet (Clouddy23) | `main` |
| Modèle de données (BDD + entités + migrations) | Filippos K. (filkat34) | `feature/datastructure` |
| CRUD Link (Entity, Form, Controller, Twig) | Mathieu L. (mathleys) | `feature/crud-link/...` |
| UI Twig/CSS (base) | Kamo G. (Spaghette5) | `feature/ui` |
| (Option) Back office + sécurité | Mathieu L. (mathleys) | `feature/backoffice` |
| Documentation + captures + PDF (README → PDF) | Filippos K. (filkat34) & Mathilde C. (Clouddy23) | / |

### Calendrier de suivi du projet
Une réunion visio d'équipe est prévue à chaque fin d'échéance.

| Échéance | Objectif |
|:--------:|:---------|
| 15/12 | Phase d’installation : installation Symfony 6.4 LTS, préparation du repository. |
| 16/12 | Visio d'organisation : répartition des tâches, création des issues/branches. |
| 17/12 | Phase de développement : ???. |
| 18/12 | Phase de relecture : Review et correction des branches (PR). |
| 19/12 | Fin du projet : Tests manuels fonctionnels, fusion des branches vers `main`, finalisation du PDF. |

## Développement du projet

### Installation de Symfony CLI 6.4 LTS (MacOS + Terminal)

**Outils utilisés** : 

PHP + Composer + Symfony CLI + SQLite + DB Browser.
Tous les outils doivent être disponibles “globalement” dans le terminal depuis n’importe quel dossier.

**1) Installation et vérification de la version de Homebrew (gestionnaire de paquets)**

``/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
brew --version
brew --version``

**2) Installation et vérification de la version PHP**

``brew update
brew install php
php -v``

**3) Vérification des extensions PHP requises (ZIP, SQLite, PDO_SQLITE**

``php -m | grep -E "zip|sqlite|pdo_sqlite"``

Le cas échéant, procéder à l'activation des extensions (suppression ";") :

``php --ini
extension=zip
extension=pdo_sqlite
extension=sqlite3``

**4) Installation, mise à jour et vérification de la version de Composer (gestionnaire de dépendances PHP)**

``brew install composer
sudo composer self-update
composer -V``

**5) Installation et vérification de Symfony CLI et de ses prérequis**

``brew install symfony-cli/tap/symfony-cli
symfony -V
symfony check:requirements``

**6) Installation DB Browser pour SQLite (outil de visualisation BDD)**

``brew install --cask db-browser-for-sqlite``

### Tests manuels fonctionnels

### Webographie

- Installation de PHP : [https://www.php.net/downloads.php](https://www.php.net/downloads.php)
- Installation de Composer : [https://getcomposer.org/](https://getcomposer.org/)
- Installation de Symfony CLI : [https://symfony.com/download](https://symfony.com/download)
- Installation de DB Browser pour SQLite : [https://sqlitebrowser.org/](https://sqlitebrowser.org/)
- Calendrier des releases Symfony : [https://symfony.com/releases](https://symfony.com/releases)
- Package - Symfony Demo : [https://packagist.org/packages/symfony/demo](https://packagist.org/packages/symfony/demo)
- Démarrage de Symfony : [https://symfony.com/doc/current/setup.html](https://symfony.com/doc/current/setup.)






