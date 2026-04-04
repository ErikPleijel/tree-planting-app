# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

**Start local dev (all services in parallel):**
```bash
composer run dev
```
This runs Laravel server, queue worker, Pail log viewer, and Vite together.

**Run tests:**
```bash
composer run test
# or a single test file:
php artisan test --filter ExampleTest
```

**Build frontend assets:**
```bash
npm run build
```

**Database:**
```bash
php artisan migrate
php artisan migrate:fresh --seed   # wipe and reseed
php artisan db:seed --class=RolesSeeder
```

**Code style (Laravel Pint):**
```bash
./vendor/bin/pint
```

## Architecture

### Domain model
- **PlantingLocation** — a physical GPS-tagged site (has `public_code` auto-generated on create, used for public QR URLs). Belongs to a Division.
- **TreePlanting** — individual planting event at a location (tree type, count, date, status).
- **Inspection** — health check record tied to a PlantingLocation.
- **Picture** — photo attached to a PlantingLocation; has a `show_on_welcome` flag for the homepage carousel.
- **Division** — organisational grouping (stores `LGA_name`).
- **TreeType**, **PlantingLocationStatus**, **TreePlantingStatus** — lookup/reference tables.

### Roles (Spatie Laravel Permission)
Four roles in ascending privilege: `Grower` → `Monitor` → `Admin` → `SuperAdmin`.  
- Routes are guarded with `role:Admin|SuperAdmin|Monitor|Grower` middleware.  
- `SuperAdmin` users cannot be edited by anyone except themselves; `Admin` cannot edit other Admins.
- Seed roles with `RolesSeeder`.

### Public access
`/p/{public_code}` — unauthenticated view of a planting location, linked from QR labels (`planting-locations.qr-label`). Handled by `PublicPlantingLocationController`.

### Map
`MapMarkerService` builds GeoJSON-style marker arrays for the Leaflet map views. Both `MapController` (stats map) and individual location views use it.

### Frontend stack
Blade + Tailwind CSS + Alpine.js, compiled with Vite. No separate JS framework.  
`resources/views/components/` holds reusable Blade components; `app/View/Components/` holds the PHP-backed ones (`AppLayout`, `GuestLayout`, `Map`).

### Key env variables
| Variable | Purpose |
|---|---|
| `INVITATION_CODE` | Static code required on the registration form to prevent open signup |
| `DB_*` | MySQL on VPS; SQLite (`database/database.sqlite`) used locally |

### Deployment
VPS is at `139.84.228.69`. Deploy via `./deploy.sh` from `/var/www/tree-planting-app`.  
Frontend build files must be copied separately: `scp -r public/build root@139.84.228.69:/var/www/tree-planting-app/public/`  
See `docs/deployment.md` for full steps and `docs/codexWorkflow.md` for the PR merge workflow.
