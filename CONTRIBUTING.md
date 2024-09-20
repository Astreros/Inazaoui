# Contribuer à Ina Zaoui

Merci de l'intérêt que vous portez à la contribution à ce projet ! Voici quelques règles et bonnes pratiques à suivre.

## Pré-requis
Avant de commencer, assurez-vous d'avoir installé les outils suivants :
- PHP >= 8.2
- Symfony >= 7.1
- Composer

## Comment contribuer

1. **Forkez le projet**.
2. **Clonez votre fork** : `git clone https://github.com/Astreros/Inazaoui`
3. **Créez une nouvelle branche** : `git checkout -b feature/nom-feature`
4. **Développez** : Faites vos modifications.
5. **Envoyez vos modifications** : `git push origin feature/nom-feature`
6. **Soumettez une Pull Request** sur la branche `main` ou `develop`.

## Règles de codage

- Respectez le standard PSR-12.
- Utilisez des messages de commits clairs et concis.
- Assurez-vous que votre code est bien testé.

## Tests
Avant de soumettre une pull request, assurez-vous que tous les tests passent :
```bash
symfony php bin/phpunit
```