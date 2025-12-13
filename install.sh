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

# Créer un .env minimal si absent
if [ ! -f .env ]; then
  echo "Création d'un .env minimal..."
  cp docker/.env.minimal .env
fi


# Afficher l’état des conteneurs
echo "Voici l’état des conteneurs :"
docker compose ps

echo ""
echo "✅ Votre application Symfony est prête !"
echo "Ouvrez votre navigateur à http://localhost:8080/"
