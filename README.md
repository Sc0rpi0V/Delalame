# Delalame

Site vitrine et back-office d'un artisan poseur de menuiseries extérieures : fenêtres, portes, portes de garage, volets.

- **Site public** : présentation de l'entreprise, des services, des réalisations et de l'équipe.
- **Demandes de devis par email** : sur la page Services, chaque service ouvre la messagerie du visiteur avec un mail pré-rempli (destinataire, objet avec la référence du service, message pré-rédigé à compléter).
- **Back-office** : l'artisan gère tout le contenu du site sans toucher au code.

## Stack

| | |
|---|---|
| Back | PHP 8.2, Symfony 7.1, Doctrine ORM, MySQL 8 |
| Back-office | EasyAdmin 4, Quill (éditeur de texte riche) |
| Front | Twig, Tailwind CSS 3, Alpine.js, Webpack Encore, Leaflet / OpenStreetMap |
| Images | VichUploader (upload), LiipImagine (miniatures) |
| Environnement local | [ddev](https://ddev.com) (PHP, nginx, MySQL, Node 20, Mailpit) |
| Tests | PHPUnit 11 (tests fonctionnels) |

## Installation

Prérequis : [ddev](https://ddev.readthedocs.io/en/stable/users/install/) et un moteur Docker (OrbStack, Docker Desktop…).

```bash
git clone https://github.com/Sc0rpi0V/Delalame.git
cd Delalame
make install
```

`make install` crée le `.env` à partir de `.env.dist`, génère un `.env.dev.local` (secret applicatif et compte admin de dev aléatoires), démarre ddev, installe les dépendances PHP et JS, compile les assets, crée la base et charge les données de démonstration.

Les identifiants du back-office en local sont dans `.env.dev.local` (`ADMIN_EMAIL` / `ADMIN_PASSWORD`).

| | |
|---|---|
| Site | https://artisan.ddev.site:8443 |
| Back-office | https://artisan.ddev.site:8443/admin |
| Mails envoyés en local | `ddev mailpit` |

Les ports 8080/8443 permettent de faire tourner le projet en même temps que d'autres projets ddev sur 80/443.

## Commandes courantes

```bash
make start        # démarrer
make stop         # arrêter
make help         # liste complète
```

| Commande | Rôle |
|---|---|
| `make cc` | Vider le cache Symfony |
| `make migrate` | Générer puis appliquer une migration Doctrine |
| `make db-setup` | Recréer la base de dev (migrations + fixtures) |
| `make db-test` | Recréer la base de test `db_test` |
| `make test` | Lancer les tests |
| `make logs` | Logs du conteneur web (nginx, PHP, Messenger, Encore) |
| `make shell` | Shell dans le conteneur web |
| `make assets-build` | Compiler les assets pour la production |

Pour le reste : `ddev console …`, `ddev composer …`, `ddev npm …`, `ddev mysql`.

Le watch Webpack Encore et le worker Messenger tournent automatiquement en arrière-plan dans ddev (`web_extra_daemons` dans `.ddev/config.yaml`).

## Fonctionnalités

### Site public

| Page | URL |
|---|---|
| Accueil | `/` |
| Services (+ boutons de demande de devis par mail) | `/services` |
| Réalisations (galerie filtrable, lightbox) | `/realisations` |
| Notre équipe | `/notre-equipe` |
| Contact (coordonnées, carte, horaires) | `/contact` |
| Pages légales | `/mentions-legales`, `/conditions-generales-de-vente`, `/conditions-generales-utilisation`, `/politique-de-confidentialite` |
| SEO | `/sitemap.xml`, `/robots.txt` |

Référencement : titre et description par page, URL canonique, Open Graph / Twitter Card, données structurées schema.org.

### Back-office (`/admin`)

| Rubrique | Contenu |
|---|---|
| Services & mails de devis | Services de la page Services : nom, référence, présentation, points clés, **message pré-rédigé du mail de devis**, ordre, publication |
| Galerie | Photos de réalisations : catégorie, texte alternatif, mise en avant |
| Notre équipe | Texte de présentation et membres |
| Pages légales | Mentions légales, CGV, CGU, politique de confidentialité |
| Page d'accueil | Accroche, domaines, atouts, chiffres clés |
| Coordonnées & horaires | Adresse (géolocalisée automatiquement sur la carte), téléphone, **email destinataire des demandes de devis**, horaires |

### Demandes de devis

Il n'y a pas de formulaire sur le site. Le bouton de chaque service est un lien `mailto:` construit dans `templates/services/index.html.twig` :

- **destinataire** : email saisi dans *Coordonnées & horaires* ;
- **objet** : `Demande de devis — <RÉFÉRENCE> · <Nom du service>` ;
- **corps** : message pré-rédigé du service, puis une zone « Informations complémentaires » à compléter par le visiteur.

Aucune donnée visiteur n'est stockée : la demande arrive directement dans la boîte mail de l'entreprise.

## Structure

```
.ddev/            Config ddev (PHP, nginx : en-têtes de sécurité, daemons)
assets/           JS et CSS (Tailwind) compilés par Webpack Encore
config/           Configuration Symfony
migrations/       Migrations Doctrine
public/           Racine web
src/
  Controller/     Pages publiques
  Controller/Admin/  Back-office EasyAdmin
  DataFixtures/   Données de démonstration
  Entity/         Service, GalleryImage, TeamMember, LegalPage, SiteContent, User
  Service/        Géocodage d'adresse (Nominatim)
  Twig/           Variable globale `site_settings` (contenus éditables)
templates/        Vues Twig
tests/Functional/ Tests fonctionnels
```

Les contenus éditables simples (textes de l'accueil, coordonnées, horaires…) sont stockés en clé/valeur dans l'entité `SiteContent` et exposés à tous les templates via `site_settings`.

## Configuration

| Fichier | Versionné | Rôle |
|---|---|---|
| `.env.dist` | oui | Modèle : noms des variables, aucune valeur sensible |
| `.env` | non | Copie du modèle, créée par `make install` |
| `.env.local` | non | Généré par ddev à chaque démarrage : connexion à la base, Mailpit |
| `.env.dev.local` | non | Généré par `make install` : `APP_SECRET`, `ADMIN_EMAIL`, `ADMIN_PASSWORD` |
| `.env.test.local` | non | Généré par `make db-test` |

**Aucun identifiant ni secret n'est versionné.** Seul `.env.dist` est commité ; tous les autres `.env*` sont bloqués par le `.gitignore`.

Variables principales : `DATABASE_URL`, `MAILER_DSN`, `APP_SECRET`, `DEFAULT_URI`, `MESSENGER_TRANSPORT_DSN`, et en dev `ADMIN_EMAIL` / `ADMIN_PASSWORD` (compte créé par les fixtures).

## Tests

```bash
make db-test   # une fois, ou après une nouvelle migration
make test
```

`make db-test` crée la base `db_test` dans le conteneur ddev et génère `.env.test.local`. Le reste de la configuration de test est dans `phpunit.dist.xml`.

## Mise en production

- Renseigner un vrai `APP_SECRET`, `DATABASE_URL`, `MAILER_DSN` et `DEFAULT_URI` dans `.env.local` (ou en variables d'environnement), avec `APP_ENV=prod`.
- `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, `php bin/console doctrine:migrations:migrate`.
- Créer le compte admin : les fixtures ne sont pas chargées en production et il n'existe pas encore de commande dédiée. Générer le hash avec `php bin/console security:hash-password`, puis insérer l'utilisateur dans la table `user` avec le rôle `["ROLE_ADMIN"]`.
- Depuis le back-office, renseigner *Coordonnées & horaires* (dont l'email destinataire des devis) et créer les services.
- Reprendre les en-têtes de sécurité de `.ddev/nginx/security-headers.conf` dans la config du serveur web, et activer HSTS une fois le HTTPS en place.
