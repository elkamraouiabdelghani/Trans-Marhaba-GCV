# Gestion des Conducteurs et Violations (GCV)

Application web pour la gestion et le suivi des conducteurs et des infractions routières pour le département transport de MARHABA.

## 1) Objectif
- Centraliser les informations des conducteurs et leurs violations.
- Offrir des tableaux de bord clairs, filtres avancés, et exports (PDF/CSV).
- Suivre le plan d’action et les évidences par violation.

## 2) Architecture UI/UX
- Barre de navigation supérieure (full-width):
  - Gauche: Logo MARHABA.
  - Centre: Liens principaux: Dashboard, Drivers, Violations, Reports.
  - Droite: Recherche (optionnel), Profil utilisateur.
- Sidebar verticale (gauche, fixe):
  - Dashboard
  - All Drivers
  - Violations by Type
  - Score points permis des chauffeurs
  - Driving Time Reports
  - Export Center
  - Plan d’action
- Zone de contenu centrale: dynamique selon la section.
- Responsive: Desktop-first, mobile-friendly (offcanvas sidebar sur mobile).

## 3) Pages & Composants
### 3.1 Dashboard (Violations Overview)
- En-tête: Titre + sélecteur de plage de dates.
- Cartes de synthèse:
  - Total conducteurs
  - Violations cette semaine
  - Conducteurs dépassant heures légales
  - Conducteur avec le plus de violations
- Graphiques:
  - Répartition des violations par type (camembert/barres)
  - Évolution des violations (ligne/barres) par jour/semaine
  - Optionnel: Carte/heatmap des violations

### 3.2 Détails du Conducteur
- Boîte d’info: nom, photo/icône, N° licence, nationalité
- KPIs: heures de conduite semaine, total violations
- Filtres: type, dates, sévérité
- Timeline/Gantt: activité quotidienne (conduite/repos) + marqueurs violations (tooltip détails)
- Tableau des violations: Date, Heure, Type, Règle, Sévérité (tri/recherche/pagination)
- Actions: Export PDF/CSV, commentaires/notes superviseur

### 3.3 Violations par Type
- Liste/graphique par catégories (ex: excès de vitesse, repos, temps de conduite)
- Filtres de période et sévérité
- Liens vers détails conducteurs

### 3.4 Score points permis des chauffeurs
- Calcul/affichage du score (définir barème)
- Alerte pour scores critiques

### 3.5 Driving Time Reports
- Rapports d’heures de conduite/repos (journalier/hebdo)
- Détection dépassements réglementaires

### 3.6 Export Center
- Export global ou par filtre en CSV/PDF
- Historique des exports (optionnel)

### 3.7 Plan d’action
- Pour chaque violation: analyse + plan d’action + évidences (fichiers)
- Suivi d’état: ouvert/en cours/résolu

## 4) Données & Modèle (proposition)
- Drivers (`drivers`)
  - id, name, license_number, nationality, photo_url, created_at, updated_at
- Violations (`violations`)
  - id, code, type (enum), rule_broken, severity (enum: low/medium/high), description
- Driver Violations (`driver_violations`)
  - id, driver_id, violation_id, occurred_at (datetime), location (text/geo),
    analysis (text), action_plan (text), evidence_path (json), status (enum)
- Driving Sessions (`driving_sessions`) (optionnel pour timeline/Gantt)
  - id, driver_id, start_at, end_at, duration_minutes, source
- Notes/Comments (`violation_notes`) (optionnel)
  - id, driver_violation_id, author_id, note, created_at

Clés/FK/Index: FK sur `driver_id`, `violation_id`. Index sur `occurred_at`, `type`, `severity`.

## 5) Rôles & Sécurité (à confirmer)
- Rôles existants: admin, responsible, teacher, student (à adapter: admin, supervisor, viewer)
- Accès:
  - Admin: CRUD global, configuration
  - Supervisor: CRUD drivers/violations, exports, plans d’action
  - Viewer: lecture, filtres, exports
- Auth Laravel (déjà présent) + policies/gates par ressource.

## 6) Fonctionnalités transversales
- Filtres & recherche dans tables/graphes (server-side si gros volumes)
- Export PDF/CSV (DOMPDF/laravel-dompdf, League CSV)
- Design moderne (Tailwind/Bootstrap), composants accessibles (a11y)
- Graphiques interactifs (Chart.js/ECharts), timeline (vis-timeline ou Chart.js + plugin)
- Localisation i18n (fr/ar/en) optionnelle

## 7) API & Routes (brouillon)
- Web (Blade):
  - GET `/dashboard`
  - GET `/drivers`, GET `/drivers/{driver}`
  - GET `/violations`, GET `/violations/types/{type}`
  - GET `/reports/score`, GET `/reports/driving-time`
  - GET `/exports`, POST `/exports`
  - GET `/action-plans`, POST `/action-plans` (créer/mettre à jour)
- API (json) optionnel pour graphiques/SPA:
  - `/api/metrics/overview?from=&to=`
  - `/api/drivers?search=&page=`
  - `/api/violations?type=&severity=&from=&to=&page=`
  - `/api/drivers/{id}/timeline?from=&to=`
  - `/api/reports/score?from=&to=`

## 8) Stack Technique
- Backend: Laravel 10+
- Frontend: Blade + Bootstrap 5 / Tailwind, Icons (Bootstrap Icons)
- Charting: Chart.js ou Apache ECharts
- PDF: barryvdh/laravel-dompdf (ou snappy/wkhtmltopdf)
- CSV: league/csv ou built-in export
- DB: MySQL/PostgreSQL

## 9) Installation & Démarrage
```bash
# Cloner
git clone <repo> && cd GCV

# Dépendances PHP & Node
composer install
npm install

# Env
cp .env.example .env
php artisan key:generate
# Configurer DB dans .env

# Migrations & seeders
php artisan migrate --seed

# Build assets
npm run dev   # ou npm run build pour prod

# Lancer serveur
php artisan serve
```

## 10) Données de test (seed) – à prévoir
- 10-20 drivers
- 5-8 types de violations
- 200+ `driver_violations`
- Sessions de conduite réalistes sur 2-4 semaines

## 11) Stratégie d’Export
- CSV: exports filtrés par période/type/sévérité
- PDF: rapports Dashboard, fiche conducteur, plan d’action par violation
- Nommage fichiers: `gcv_<section>_<YYYYMMDD_HHMM>.{csv|pdf}`

## 12) Performance & Qualité
- Pagination server-side (tables)
- Index BDD sur colonnes de filtre/tri
- Jobs asynchrones pour exports lourds
- Tests: Feature (routes, filtres, exports), Unit (calculs score)

## 13) Roadmap (itérative)
- Sprint 1: Structure UI (navbar + sidebar + layout), modèles & migrations, listing Drivers
- Sprint 2: Saisie/Import violations, Dashboard (KPIs + graphes de base)
- Sprint 3: Détails conducteur (timeline + tableau), exports CSV/PDF
- Sprint 4: Violations par type, Driving Time Reports, Score permis
- Sprint 5: Plan d’action par violation + upload d’évidence, Rôles & permissions
- Sprint 6: Optimisations, QA, documentation utilisateur

## 14) Définition de Fini (DoD)
- UX responsive, navigation fluide
- Filtres fonctionnels et testés
- Exports valides et vérifiés
- Graphiques cohérents avec données
- Sécurité (auth + autorisations) en place
- Documentation mise à jour (README + guide utilisateur)

## 15) Notes d’implémentation sidebar/navbar
- La navbar supérieure expose les liens principaux; la sidebar reste le menu détaillé.
- Le logo/titre se masque lorsque la sidebar est réduite (déjà implémenté dans `resources/views/layouts/navigation.blade.php`).
- Le bouton de toggle est dans l’en-tête de la sidebar et synchronisé avec `localStorage`.

---
Ce README est la base de cadrage du projet GCV. Il peut être adapté au fur et à mesure des retours métier et des contraintes techniques.


