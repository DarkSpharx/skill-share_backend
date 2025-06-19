# Skill Share Backend

Bienvenue dans le backend de Skill Share, une plateforme de partage de compétences.

## Fonctionnalités

- Gestion des utilisateurs (inscription, connexion, profils)
- Création et gestion des offres de compétences
- Système de réservation et de messagerie
- API RESTful sécurisée
- Authentification par JWT
- Validation des données côté serveur
- Gestion des rôles et autorisations

## Prérequis

- [Node.js](https://nodejs.org/)
- [npm](https://www.npmjs.com/)
- [MongoDB](https://www.mongodb.com/)

## Installation

```bash
git clone https://github.com/votre-utilisateur/skill-share-backend.git
cd skill-share-backend
npm install
```

## Configuration

Créez un fichier `.env` à la racine du projet avec vos variables d'environnement :

```env
PORT=3000
MONGODB_URI=your_mongodb_uri
JWT_SECRET=your_jwt_secret
```

## Lancement du serveur

```bash
npm start
```

Le serveur sera disponible sur [http://localhost:3000](http://localhost:3000).

## Structure du projet

```
/src
    /controllers   # Logique métier et gestion des requêtes
    /models        # Schémas Mongoose pour MongoDB
    /routes        # Définition des routes API
    /middlewares   # Middlewares personnalisés (auth, validation, etc.)
```

## Contribution

Les contributions sont les bienvenues ! Veuillez ouvrir une issue ou une pull request.

## Licence

Ce projet est sous licence MIT.
