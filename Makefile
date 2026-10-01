.PHONY: help install start stop restart logs logs-php shell cc migrate db-setup db-test assets-watch assets-build test

help: ## Afficher l'aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# Aucun identifiant dans ce fichier : ils sont générés dans des .env.*.local (jamais versionnés).
RANDOM_HEX = $$(openssl rand -hex 16)

install: ## Installer le projet complet (première fois)
	@test -f .env || cp .env.dist .env
	@test -f .env.dev.local || { \
		printf 'APP_SECRET=%s\nADMIN_EMAIL=admin@example.test\nADMIN_PASSWORD=%s\n' "$(RANDOM_HEX)" "$(RANDOM_HEX)" > .env.dev.local; \
		echo "→ .env.dev.local créé (compte admin de dev généré, voir ce fichier)"; }
	ddev start
	ddev composer install --no-interaction
	ddev npm install
	ddev npm run dev
	ddev console doctrine:migrations:migrate --no-interaction
	ddev console doctrine:fixtures:load --no-interaction
	ddev exec supervisorctl restart webextradaemons:
	@echo "\n✅ Projet prêt sur https://artisan.ddev.site:8443"
	@echo "   Admin : https://artisan.ddev.site:8443/admin (identifiants dans .env.dev.local)"
	@echo "   Mails : ddev mailpit"

start: ## Démarrer le projet
	ddev start

stop: ## Arrêter le projet
	ddev stop

restart: ## Redémarrer le projet
	ddev restart

logs: ## Afficher les logs (web : nginx, php-fpm, messenger, encore)
	ddev logs -f

logs-php: ## Afficher les logs Symfony
	ddev exec tail -f var/log/dev.log

shell: ## Ouvrir un shell dans le conteneur web
	ddev ssh

cc: ## Vider le cache Symfony
	ddev console cache:clear

migrate: ## Générer et appliquer les migrations
	ddev console doctrine:migrations:diff
	ddev console doctrine:migrations:migrate --no-interaction

db-setup: ## (Re)créer la BDD + migrations + fixtures
	ddev console doctrine:database:drop --force --if-exists
	ddev console doctrine:database:create
	ddev console doctrine:migrations:migrate --no-interaction
	ddev console doctrine:fixtures:load --no-interaction

db-test: ## (Re)créer la BDD de test (db_test)
	@grep '^DATABASE_URL' .env.local > .env.test.local
	@echo "APP_SECRET=$(RANDOM_HEX)" >> .env.test.local
	ddev exec -s db 'mysql -e "DROP DATABASE IF EXISTS db_test; CREATE DATABASE db_test; GRANT ALL ON db_test.* TO \`db\`@\`%\`;"'
	ddev console --env=test doctrine:migrations:migrate --no-interaction

test: ## Lancer les tests PHPUnit
	ddev exec php bin/phpunit

assets-watch: logs ## Watch Encore : tourne en daemon ddev, sortie dans les logs

assets-build: ## Compiler les assets (prod)
	ddev npm run build
