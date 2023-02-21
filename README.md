# migration

migration est un utilitaire de migration de structure de base de données en ligne de commande. Il a été pensé pour être le plus simple. Pas besoin d'utiliser une 'langage' spécifique à l'utilitaire, paramétrez la connexion à votre base de données, créez votre script de migration en SQL et c'est parti.

## Installation

installation avec composer:

```shell
composer require unofficialmc2/migration
```

Une fois installé, créer un fichier de configuration

```shell
./vendor/bin/migration --init
```

## Paramétrage

le paramétrage se fait dans le fichier de configuration.

## Script de migration

Ecrire dans les sous dossier provider se trouvants dans le dossier de migration les script de migration correspondant au provider.

Séparer les requête SQL par une ligne où on a une série de 3 tirets (`---`).

exemple :

```sql
CREATE TABLE user (
    id INTEGER PRIMARY KEY
);
---
CREATE TABLE entity (
    id INTEGER PRIMARY KEY
);
```
