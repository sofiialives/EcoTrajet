# Eco Trajet

Application de covoiturage étudiant (projet universitaire) — Symfony 7.4 + MySQL + Bootstrap 5, entièrement en français.

Les étudiants publient des trajets, les passagers les recherchent, filtrent et réservent des places. Thème sombre, accent lime, conçu à partir de la maquette Figma.

## Stack technique

| Composant | Choix |
|---|---|
| Framework | Symfony 7.4 (PHP 8.2) |
| Base de données | MySQL 8.0 (Doctrine ORM) |
| Frontend | Bootstrap 5.3 (CDN) + thème CSS maison par-dessus |
| Mails (dev) | MailDev (SMTP local, interface sur `:1080`) |
| Conteneurs | Docker Compose (`app`, `db`, `phpmyadmin`, `maildev`) |

## Lancer le projet

```bash
docker compose up -d --build
docker compose exec app php bin/console doctrine:database:create --if-not-exists
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

- Application : http://localhost:8000
- phpMyAdmin : http://localhost:8080 (user `root` / `root`)
- MailDev (mails de réinitialisation de mot de passe) : http://localhost:1080

### Important : `.env.local`

Les vraies valeurs de connexion (`DATABASE_URL`, `MAILER_DSN`) vivent dans **`.env.local`**, pas dans `.env`. C'est volontaire : `.env.local` n'est jamais suivi par git et jamais réécrit par Symfony Flex lors d'un `composer install`/`composer require`, contrairement aux blocs `###> ... ###` de `.env` qui reviennent à leurs valeurs par défaut (Postgres) dès qu'un paquet est réinstallé. Si `.env.local` n'existe pas sur une nouvelle machine, le créer avec :

```bash
cat > .env.local << 'EOF'
DATABASE_URL="mysql://symfony:symfony@db:3306/symfony?serverVersion=8.0&charset=utf8mb4"
MAILER_DSN=smtp://maildev:1025
EOF
```

## Structure

```
src/
  Entity/        Utilisateur, Trajet, Reservation
  Repository/     requêtes (recherche de trajets, statuts, etc.)
  Form/           formulaires Symfony (inscription, trajet, réservation, profil...)
  Service/        logique métier (réservation, gestion des trajets, mot de passe, upload photo)
  Controller/     routes HTTP
  Enum/           StatutTrajet, StatutReservation
templates/
  securite/       connexion, inscription, mot de passe oublié/réinitialisation
  trajet/         recherche, détails, création/modification
  reservation/    réservation et confirmation
  mes_trajets/    trajets créés + réservations (onglets)
  profil/         profil + changement de mot de passe
  form/theme.html.twig   thème de formulaire (base Bootstrap 5 + icône œil pour les mots de passe)
assets/styles/app.css    thème visuel (tokens de couleur, composants, surcouche Bootstrap)
migrations/      schéma de base (utilisateur, trajet, reservation)
```

## Formulaires

Tous les champs sont rendus explicitement (pas de `form_row`) :

```twig
{{ form_label(form.email, 'Adresse e-mail', {label_attr: {class: 'form-label fw-semibold'}}) }}
{{ form_widget(form.email, {attr: {class: 'form-control', placeholder: '...'}}) }}
{{ form_errors(form.email) }}
```

Le thème (`templates/form/theme.html.twig`) étend `bootstrap_5_layout.html.twig` et n'ajoute que l'icône œil pour les mots de passe (`data-oeil` sur l'attribut du champ) et le rendu des erreurs. Les styles des champs eux-mêmes (fond, bordure, focus) sont définis une seule fois dans `assets/styles/app.css`, section « Harmonisation Bootstrap 5 », sur les classes `.form-control` / `.form-select` / `.form-label` / `.form-check-input` — à modifier là, et nulle part ailleurs.

## Logique métier notable

- **Statuts de trajet** (`StatutTrajet`) : à venir → en cours → terminé, ou annulé. Recalculés automatiquement à l'affichage (`TrajetRepository::rafraichirStatuts`).
- **Places restantes** : calculées à la volée (places totales − réservations actives), jamais stockées.
- **Réservation** (`ReservationService`) : vérifie trajet non passé, places suffisantes, pas de double réservation, puis confirme directement (pas de paiement en ligne, réglé entre étudiants).
- **Mot de passe oublié** : jeton aléatoire + expiration 1h, e-mail envoyé via MailDev en dev.

## Commandes utiles

```bash
# Logs de l'app
docker compose logs -f app

# Shell dans le conteneur
docker compose exec app bash

# Vider le cache
docker compose exec app php bin/console cache:clear

# Nouvelle migration après modification d'une entité
docker compose exec app php bin/console doctrine:migrations:diff
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```
