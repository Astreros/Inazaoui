# Ina Zaoui

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