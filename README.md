# Ina Zaoui

## Description du projet

Ce projet Symfony est un site web pour la photographe Ina Zaoui. Il lui permet de mettre en ligne et de gérer ses photos, mais également d'avoir des utilisateurs invités, avec un espace de gestion limité à leurs propres images.
L'administrateur du site dispose d'un espace d'administration pour gérer l'ensemble des photos, gérer l'ensemble des albums et gérer l'ensemble des utilisateurs (création, modération, suppression).

## Structure du projet

Les controller sont structurés en deux parties, tout d'abord un HomeController et SecurityController pour gérer les pages public du site et une partie Admin pour les pages accessible après connexion. 
Les tests reprennent la même structure.  

SecurityController s'occupe de l'affiche de la page de connexion et de la route /logout. Security/AppCustomAuthenticator s'occupe de la logique de connexion personnalisée.  

## Pré-requis
* PHP >= 8.2
* Composer
* Extension PHP XDebug

## Installation

Installation des dépendances :
```bash
composer install
```

## Configuration

Créer le fichier `.env.local` et configurer l'accès à la base de données. Exemple pour une base de données MySQL :
```
DATABASE_URL="mysql://root:Password@127.0.0.1:3306/inazaoui?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```

## Usages

### Base de données

#### Supprimer la base de données
```bash
symfony console doctrine:database:drop --force --if-exists
```

#### Créer la base de données
```bash
symfony console doctrine:database:create
```

#### Exécuter les migrations
```bash
symfony console doctrine:migrations:migrate -n
```

#### Charger les fixtures
```bash
symfony console doctrine:fixtures:load -n
```

*Note : Vous pouvez exécuter ces commandes avec l'option `--env=test` pour les exécuter dans l'environnement de test.*


### Test

#### Exécuter les tests
```bash
symfony php bin/phpunit
```

#### Création rapport de couverture du code
```bash
symfony php bin/phpunit --coverage-html public/test-coverage
```

#### Exécuter les tests PHPStan
```bash
vendor/bin/phpstan analyse
```