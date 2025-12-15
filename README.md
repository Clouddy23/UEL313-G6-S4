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

| Étudiant.e  | Alias      | % participation |
|:----------:|:----------:|:---------------:|
| Mathilde C.| Clouddy23  |                 |
| Kamo G.    | Spaghette5 |                 |
| Mathieu L. | mathleys   |                 |
| Filippos K.| filkat34   |                 |

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

| Echéance | Objectif |
|:--------:|:---------|
| 15/12 | Phase d'installation et de réflexion : installation Symfony 6.4 LTS, choix techniques, préparation du reposity, choix techniques (BDD, UI...) et répartition des tâches et création des issues/branches. |
| 16/12 | Phase de développement : mise en place du modèle + début CRUD sur des branches distinctes. |
| 17/12 | Phase de développement : CRUD complet, validations formulaire, messages utilisateur, UI |
| 18/12 | Relecture des branches (PR), corrections, fusion vers `main` et tests manuels fonctionnels (parcours CRUD) |
| 19/12 | Finalisation du rendu : captures écran, schémas, rédaction et export du PDF, ajout des ressources et % participation. |

## Développement du projet

## Tests manuels fonctionnels

## Licence
Projet pédagogique — usage formation.
