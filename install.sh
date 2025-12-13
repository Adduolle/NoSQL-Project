#!/bin/bash

# Stopper tout conteneur en cours (optionnel)
# docker compose down

# Monter les conteneurs en arrière-plan
echo "Démarrage des conteneurs..."
docker compose build php
docker compose up -d

# Installer les dépendances Symfony
echo "Installation des dépendances Symfony dans le conteneur PHP..."
docker compose exec php composer install

# Afficher l’état des conteneurs
echo "Voici l’état des conteneurs :"
docker compose ps

echo ""
echo "✅ Votre application Symfony est prête !"
echo "Ouvrez votre navigateur à http://localhost:8080/"
