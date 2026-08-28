# Decisions Log — itacenmu.org

This file records architectural and strategic decisions for the tree-planting
app, including the reasoning behind them, so future changes (by Erik, by
Claude Code, or by anyone else) can understand *why* something was built a
certain way, not just *what* was built.

Format: newest entries at the top. Each entry has a date, a short title,
the context that prompted it, the decision, and the reasoning.

---

## 2026-08-28 — LiDAR Scan Upload — Discoverability Fix

**Context**

The LiDAR scan upload feature (migrations → model → controller →
views/routes, see "LiDAR Integration — Investigation & Proposal" and
"LiDAR Scan Upload — Implementation Judgment Calls" below) had no
visible entry point anywhere in the UI. A dedicated diagnosis pass
traced the actual cause, evidence-based rather than assumed:

- **Coverage gap**: the only existing link into `lidar-scans.create`
  lived *inside* the per-measurement-row loop on
  `tree-planting-measurements/index.blade.php` — rendered once per
  existing `TreePlantingMeasurement` row. Confirmed via a real
  authenticated request that a `TreePlanting` with **zero** recorded
  measurements shows only the "No measurements recorded yet." message,
  with no `lidar-scans` reference anywhere on that page — no path to
  the upload form at all in that state.
- **Prominence gap**: even where it existed, the link was a bare
  `text-xs text-blue-600 hover:underline` text link stacked in a
  narrow table cell — easy to miss — several clicks deep (Location
  show page → a specific TreePlanting's Measurements page), with no
  higher-level entry point anywhere (not on the Location show page,
  not on the dashboard, not as its own nav item).

Also confirmed, before writing any code, that `tree-plantings.show`
(the "likely" TreePlanting-level view) is never linked from anywhere
in the app's actual views — it's reachable only by a direct URL a user
would have to already know. The real navigation path is
`planting-locations/show.blade.php`'s "Trees Planted" table, where
each `TreePlanting` row already has its own action buttons
(📏 Measurements, 🤝 Contributors, Edit, Delete) — that row is the
genuine "TreePlanting level" UI, not a separate show page.

**Decision**

Added two new, properly-styled buttons (not text links) — both use the
upload-first flow (`lidar-scans.create` with no
`tree_planting_measurement_id`), confirmed working end-to-end against
the current `LidarScanController` before relying on it (see below):

- `planting-locations/show.blade.php`: a "📡 Attach Scan" pill in the
  Trees Planted table's per-row Actions cell, matching that row's
  existing button convention exactly (same `px-2 py-1 text-xs rounded`
  sizing as Measurements/Contributors/Edit, a new indigo color to stay
  visually distinct from the existing blue/teal/yellow/red).
- `tree-planting-measurements/index.blade.php`: a "📡 Attach LiDAR
  Scan" button next to the existing "➕ New Measurement" button, in the
  always-visible top action row — placed *above* the
  `@if($measurements->isEmpty())` check, so it renders regardless of
  whether the `TreePlanting` has any measurements yet. This is the
  direct fix for the coverage gap.

The existing per-row "+ Attach Scan" text links (pre-linked to a
specific measurement) are unchanged — both flows now coexist. A scan
tied to an already-recorded measurement is still a legitimate,
narrower flow; the two new buttons are a second, broader entry point
that doesn't depend on any measurement existing.

**`LidarScanController` was not modified — verified, not assumed,
that it didn't need to be.** Before writing the new buttons, tested
the upload-first flow directly: submitted a request to `store()` with
`tree_planting_measurement_id` omitted entirely (not even `null` —
genuinely absent from the input), and confirmed a real `lidar_scans`
row was created with `tree_planting_measurement_id = NULL`, matching
the original nullable-FK design intent from the LiDAR Integration
proposal. `create()` already handles the same omitted-param case via
its existing `if ($measurementId)` guard and the view's `@else`
branch. No controller or route changes were needed or made.

**Reasoning**

Keeping both entry points (broad, unlinked upload vs. narrow,
pre-linked-to-a-measurement upload) rather than collapsing to one
avoids forcing a scan to have a measurement before it can be recorded,
which was the whole point of the nullable FK in the original design —
a scan can legitimately be captured before its measurement is
finalized. Matching each button's styling to its own page's existing
convention (the row-level pill size on the Location page, the
top-action-row button size on the Measurements page) keeps the fix
visually consistent rather than introducing a new one-off style.

## 2026-08-26 — Computed area/density on the planting-location show page

**Context**

`app/Services/PolygonAreaCalculator.php`, its wiring into
`PlantingLocationController::show()`, the restructured "Location Data"
section on `resources/views/planting-locations/show.blade.php`, and
`tests/Unit/PolygonAreaCalculatorTest.php` were built during an earlier
investigation task that was supposed to be read-only — they were never
intended as that task's deliverable. This entry exists to properly
document the feature after the fact, rather than leave it sitting
undocumented.

Checked before deciding whether to keep or discard it:
- **Already committed**, not sitting as an uncommitted working-tree
  change as originally assumed — commit `1297ba1`, containing exactly
  these four files and nothing else (a clean, already-atomic commit,
  just under this repo's generic "Commit this" message rather than a
  descriptive one).
- **Live, not inert** — wired into `planting-locations.show`
  (`GET /planting-locations/{planting_location}`), one of the
  most-visited routes in the app (every "View" link from the dashboard,
  tree-plantings index, inspections index, etc. lands here).
- **Handles the missing-boundary case correctly.** At the time this was
  built, 1 of 3 `planting_locations` rows had no `boundary_geojson`.
  `PolygonAreaCalculator::calculateHectares()` returns `null` (not
  `0.0`) when there's no ring to calculate from;
  `PlantingLocationController::show()` guards the density calculation
  against a `null` or zero area before dividing; the view renders
  "N/A" for both Area and Density rather than a misleading `0.00`.
  Confirmed via `tests/Unit/PolygonAreaCalculatorTest.php` (3/3
  passing) and by hand-calculating a known rectangle's expected area.

**Decision**

Kept as a real, documented feature — computed on read from
`boundary_geojson` each time the page loads, not stored anywhere. Not
rewriting `1297ba1`'s commit message: it's several commits back from
the current tip (not the tip itself), so rewording it would require an
interactive rebase, which is outside what's done in this workflow; this
entry is the documentation trail instead.

**Reasoning**

The area/density approximation is the same latitude-adjusted
degree-to-km approach already used by `NearbyLocationFinder`
(consistent precision expectations across the codebase, not
survey-grade), and showing it on the location page is a genuine,
low-risk improvement — it doesn't touch stored data, degrades
gracefully to "N/A" for the locations that don't have a boundary yet,
and reuses an already-eager-loaded relation (`treePlantings`) for the
total-trees figure rather than adding a new query.

## 2026-08-26 — LiDAR Scan Upload — Implementation Judgment Calls

**Context**

Two judgment calls made while building `LidarScanController::store()`
(see "LiDAR Integration — Investigation & Proposal" below for the
underlying design) that weren't dictated by the proposal itself and are
worth recording explicitly, since both are places a future change could
silently regress.

**Decision**

1. **Scan file size limit: 100MB, an explicit guess.**
   `PictureController::uploadStore()` caps photo uploads at 8MB — that
   limit is inappropriate for LiDAR mesh/point cloud exports, which run
   far larger. 100MB was set as a starting ceiling with **no basis in
   real file samples** from actual phone LiDAR app exports (Polycam,
   3D Scanner App, etc.). This needs revisiting once real scan files
   from field use are available — it may be too generous, too
   restrictive, or roughly right; there's no evidence either way yet.

2. **File type validated by extension, not Laravel's `mimes:` rule —
   and this means there is no content-level check at all.** Several
   expected scan formats (`.usdz`, `.glb`, `.las`, `.laz`) aren't in
   Laravel/Symfony's default MIME-type database, so `mimes:` would
   falsely reject legitimate uploads in those formats. The workaround —
   checking the uploaded file's extension string directly
   (`getClientOriginalExtension()`) against a fixed allow-list — means
   validation currently trusts the extension alone. There is **no
   minimum size floor, and no attempt to open or parse the file to
   confirm its contents actually match its claimed type.** A file named
   `scan.obj` that's actually empty, corrupted, or something else
   entirely would pass validation and be stored as-is.

**Reasoning**

Both are accepted tradeoffs for this first slice, not silent gaps —
recorded here specifically so they're documented rather than
rediscovered. The extension-only check in particular is deliberately
scoped to this being an internal tool used by known field verifiers
(the same small set of privileged roles that can already record
measurements), not a public upload surface — the threat model for
someone deliberately uploading a mislabeled file is low. If this tool's
audience ever broadens, or if scan files start being processed
server-side (parsed, rendered, or otherwise opened rather than just
stored and linked), this tradeoff should be revisited: at minimum a
magic-byte/content sniff before trusting the extension, and likely a
minimum size floor to catch empty/truncated uploads.

## 2026-08-26 — LiDAR Integration — Investigation & Proposal

**Status:** investigation + proposal only — no migrations, models, controllers,
or views were touched. Nothing here is built yet.

### Investigation findings

**Model/migration/controller for the measurement record:**
`App\Models\TreePlantingMeasurement`, table `tree_planting_measurements`
(`database/migrations/2026_06_02_000000_create_tree_planting_measurements_table.php`),
`App\Http\Controllers\TreePlantingMeasurementController`. Views:
`resources/views/tree-planting-measurements/{index,create,edit}.blade.php`.

**Full schema** (`tree_planting_measurements`):
| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `tree_planting_id` | FK → `tree_plantings`, cascade | measurement has no meaning without its parent |
| `measurement_date` | date | |
| `trees_surviving` | unsigned int, nullable | validated ≤ `tree_planting.number_of_trees` |
| `height_avg_cm` | decimal(6,2), nullable | |
| `dbh_avg_cm` | decimal(6,2), nullable | |
| `canopy_cover_pct` | decimal(5,2), nullable | validated 0–100 |
| `notes` | text, nullable | |
| `user_id` | FK → `users`, cascade | who recorded it |
| `verified_by_user_id` | FK → `users`, nullable, nullOnDelete | deliberately distinct from `user_id`; never the same person |
| `verified_at` | timestamp, nullable | |
| `created_at`/`updated_at` | | editable table, normal timestamps (unlike `change_logs`) |

**Related tables**, walking the actual hierarchy — there is no "tree" or
"plot" or "site" entity: `PlantingLocation` (the site) → `TreePlanting`
(a cohort/batch of `number_of_trees` trees of one `TreeType`, planted on
one date — individual trees are never tracked as separate rows) →
`TreePlantingMeasurement` (a periodic measurement of that whole cohort).
`TreeType` (species) hangs off `TreePlanting`, not the measurement.

**How Height avg / DBH avg / Canopy % are entered:** all three are plain
manual `<input type="number" step="0.01">` fields on
`tree-planting-measurements/create.blade.php` (lines ~41-68), validated
server-side as `nullable|numeric` (with `canopy_cover_pct` additionally
capped 0–100). Confirmed — not a calculated average across individual
tree records (no such records exist to average) and not derived from
any photo or sensor input. A human estimates/measures the cohort and
types in a single number for each field.

**GPS/photo provenance:** exists, but only on `Picture`
(`captured_at`, `captured_latitude`, `captured_longitude`,
`capture_source` — added in
`2026_06_04_000000_add_capture_provenance_to_pictures_table.php` —
plus `consent_confirmed_at`), which belongs to `PlantingLocation`, not
to `TreePlanting` or `TreePlantingMeasurement`. There is no FK from
`Picture` to either, and no GPS/provenance fields anywhere on the
measurement itself. `ExifExtractor` (`app/Services/ExifExtractor.php`)
is the existing provenance-reading service, but it's wired only into
`PictureController`'s upload flow.

**Audit trail pattern:** `ChangeLog` (`app/Models/ChangeLog.php`,
table `change_logs`) — polymorphic (`loggable_type`/`loggable_id`,
deliberately **not** a real FK so history survives a hard delete of the
parent), `action` string, `old_values`/`new_values` JSON, `reason`,
`changed_by` (nullable FK, nullOnDelete) + `changed_by_name` (a name
snapshot, so attribution survives even after the user is gone),
append-only (`created_at` only, no `updated_at`). Written explicitly via
`App\Services\ChangeLogger::record()` at each mutation point in
controllers — not model observers, specifically because at least one
existing mutation (`TreePlanting::whereIn(...)->update(...)` in the
"move" feature) is a query-builder mass update that wouldn't fire
Eloquent events. `TreePlantingMeasurementController` already uses this
for `measurement_updated`, `measurement_verification_reset`, and
`measurement_verified` — any new LiDAR-related mutation should log
through the same service, the same way.

### Proposal

**New fields** (see table design below): `scan_source`,
`scan_file_path`, `lidar_height_cm`, `lidar_dbh_estimate_cm`,
`lidar_canopy_area_m2`, `scan_confidence`, `method_note`,
plus a `measurement_source` column on the *existing* measurement table
(see below — this one field is the one thing I'd add to
`tree_planting_measurements` itself).

**Same record or separate table — recommendation: separate table**,
e.g. `lidar_scans`, referenced from `tree_planting_measurements`.
Reasoning, tied specifically to how `Recorded By`/`Verified` work today:

- `Recorded By` (`user_id`) and `Verified` (`verified_by_user_id` +
  `verified_at`) currently model one specific attestation shape: *one
  named person entered these numbers, a second named person confirmed
  they're right.* A LiDAR scan's provenance is structurally different —
  it's *a device produced this dataset*, which is a different kind of
  fact than *a person typed this number*. Bolting LiDAR columns
  directly onto `tree_planting_measurements` would blur those two
  distinct claims into one row's worth of nullable columns.
- Cardinality doesn't match 1:1 cleanly either: a field verifier might
  redo a scan that came out blurry, or capture multiple scans of the
  same cohort over the lifetime of one measurement date, or capture a
  scan slightly before the measurement record itself is finalized. A
  separate table with its own `id` and a nullable FK back to the
  measurement handles all of that without forcing awkward
  "which scan wins" logic on a single-row schema.
- Most measurements will likely never have LiDAR data at all (phone
  LiDAR requires specific hardware — iPhone/iPad **Pro** models only —
  so paper/manual fallback stays the norm for a long time). Adding a
  wide set of always-null columns to every measurement row is exactly
  the kind of schema bloat this app has avoided elsewhere (see
  `GeometryFingerprint`'s own comment about not storing full geometry
  on every edit "for anything beyond a handful of vertices" — same
  instinct applies here).
- This mirrors the app's own precedent: GPS/EXIF provenance already
  lives on its own table (`pictures`) rather than being jammed onto
  `planting_locations`, specifically because it's a different kind of
  fact (device/file provenance) about a related-but-distinct thing.

Proposed `lidar_scans` schema:
| Column | Type | Notes |
|---|---|---|
| `id` | bigint | |
| `tree_planting_measurement_id` | FK → `tree_planting_measurements`, nullable, nullOnDelete | nullable so a scan can be uploaded and later linked; explore tightening to required once the upload flow is proven out |
| `user_id` | FK → `users`, cascade | who captured/uploaded it — mirrors `TreePlantingMeasurement.user_id` |
| `scan_source` | string, nullable | free string, not a DB enum — matches `pictures.capture_source`'s established convention (e.g. `'phone_lidar'`, `'drone'`, `'satellite'`) |
| `scan_file_path` | string, nullable | exported mesh/point cloud/report file, stored the same way `pictures.path` is |
| `lidar_height_cm` | decimal, nullable | |
| `lidar_dbh_estimate_cm` | decimal, nullable | |
| `lidar_canopy_area_m2` | decimal, nullable | note: m² here, not the existing `canopy_cover_pct` — a LiDAR scan naturally yields an area, not a percentage; reconciling the two is a UI/reporting concern, not a schema one |
| `scan_confidence` | string, nullable | free text or a small fixed vocabulary (e.g. high/medium/low), self-reported by the capturer |
| `method_note` | text, nullable | freeform — which app was used, scan conditions, anything not worth a dedicated column |
| `created_at`/`updated_at` | | |

Plus one addition to the *existing* `tree_planting_measurements` table:
`measurement_source` (string, nullable, e.g. `'manual'` /
`'lidar_derived'` / `'lidar_assisted'`, default `'manual'` so every
existing row is unaffected). This is the one field I'd put on the
measurement itself rather than the scan table, because it's a property
of *the measurement's numbers*, not of any one scan — see verification
impact below.

**Impact on the Verified workflow:** I'd recommend **not** forking a
second verification tier. Verra/Plan Vivo's preference for objective
methods is better served by making the *source* of a measurement
queryable/reportable than by inventing a parallel "LiDAR-verified"
state machine that this codebase would then have to keep in sync with
the existing one forever. Concretely:

- `measurement_source` (above) makes "how was this row produced"
  filterable in exports/reports without touching `verified_at`'s
  meaning at all.
- The existing self-verification rule (recorder ≠ verifier,
  `TreePlantingMeasurementController::verify()`) applies identically
  regardless of source. A device doesn't verify itself — phone LiDAR is
  still a rough estimate, not survey-grade, so a second human
  confirming the transcribed numbers are plausible is exactly as
  necessary as it is for a manual measurement today.
- A stronger bar — e.g. `verify()` refusing to proceed unless a linked
  `lidar_scans` row's file is still retrievable — is worth flagging as
  a **later** enhancement, not part of a first slice. It adds a real
  dependency (file-storage health) to the verification path that the
  app doesn't have today, and isn't needed to get LiDAR data flowing
  and reportable.

**Minimal viable capture flow, phone-based (iPhone/iPad Pro):** this
app has no native mobile component and shouldn't grow one for this.
The realistic v1 flow treats the web app as a passive receiver of a
third-party scanning app's output, exactly the way `PictureController`
already receives photos:

1. Field verifier scans the cohort with an off-the-shelf LiDAR app
   (e.g. Polycam, 3D Scanner App, SiteScape, Scaniverse) on an
   iPhone/iPad **Pro**-class device (only these have a LiDAR sensor).
2. That app computes and/or exports whatever it exports (a mesh/point
   cloud file, and often its own on-device dimensional readout).
3. The verifier opens this web app on the same device, goes to a new
   "Attach LiDAR Scan" form against the relevant measurement, manually
   transcribes the number(s) the scanning app reported
   (`lidar_height_cm` etc.), picks `scan_source`, optionally rates
   `scan_confidence`, and uploads the exported file as
   `scan_file_path` — same upload mechanics as photo upload today.
4. `ChangeLogger` records `lidar_scan_added`, same pattern as every
   other mutation in this subsystem.

No native SDK integration, no in-browser 3D rendering, no server-side
point cloud parsing — this app never opens the scan file, it just
stores it and the numbers a human copied out of another app.

**Explicitly NOT part of a first slice:**
- Full point cloud storage/processing pipeline (no in-browser viewer,
  no server-side meshing/segmentation — this app's stack has no
  point-cloud tooling, e.g. no PDAL/Open3D equivalent, and adding one
  is a much bigger decision than this task).
- Drone flyover batch ingestion (multi-file import, orthomosaic
  stitching, automated per-tree canopy segmentation).
- Automatic height/DBH/canopy extraction from a raw scan file — v1
  stores whatever number a human already read off the scanning app's
  own UI, nothing is computed from the file server-side.
- A native mobile app or custom scanning SDK.
- A second/parallel verification tier (see above).

**Reasonable first slice:** the `lidar_scans` table + `user_id`/
`scan_source`/`scan_file_path`/three numeric fields/`scan_confidence`/
`method_note` as designed above; the single `measurement_source`
addition to `tree_planting_measurements`; a `LidarScanController`
mirroring `TreePlantingMeasurementController`'s structure (`index`/
`create`/`store` — no `verify()` of its own, verification stays on the
parent measurement); file upload validated the same way
`PictureController`'s upload flow already validates photo uploads;
`ChangeLogger` entries for scan uploads; and on the measurements
index/create views, a small indicator for measurements that have a
linked scan plus a link into the new upload form.

## 2026-08-26 — Introduced FontAwesome (CDN, 6.4.0), restyled planting-locations filter section to match a sibling project's design

**Context**

Restyling the Planting Locations filter section to match a design pulled
from a sibling Laravel project (`redcross_volunteers/nrcs-volunteer-database`,
`organisations/index.blade.php`). That reference's Filter/Clear buttons use
real FontAwesome icons (`fas fa-search`, `fas fa-times`). This app had no
FontAwesome loaded anywhere — everywhere else (nav hamburger/close/chevron)
uses inline SVG, and a prior investigation found `home.blade.php`'s existing
`fas fa-phone`/`fas fa-envelope` markup was already present but inert, since
no FontAwesome stylesheet was ever loaded to back it.

**Decision**

Added FontAwesome via CDN link (`cdnjs.cloudflare.com`, version 6.4.0 —
matching the reference project exactly) to `layouts/app.blade.php`'s
`<head>`, alongside the existing Leaflet CSS/JS includes. Used real
`fas fa-search` / `fas fa-times` icons directly in the filter section's
Filter/Clear buttons, rather than building one-off inline SVG for just this
task and replacing it again later — the person I'm working with plans
broader FontAwesome adoption for menu items and headings in a follow-up
task, so introducing the dependency now (rather than twice) was the better
sequencing.

Ported the reference project's `.filter-*` utility-class family
(`.filter-container`, `.filter-form`, `.filter-grid`/`-2`/`-3`/`-4`/`-5`/`-6`,
`.filter-label`, `.filter-input`, `.filter-select` (+ `-small` variants),
`.filter-active`, `.filter-btn-primary`, `.filter-btn-secondary`
(+ `-active`/`-disabled`), `.filter-actions`, `.filter-button-group`) into
this app's `resources/css/app.css`, which was previously just the three bare
`@tailwind` directives with no custom classes. Substituted this app's
`primary` theme color (`#2F855A`) for the reference's `blue-600`/`blue-700`
on `.filter-btn-primary` and the input/select focus rings — every other
color in the ported classes (gray secondary button, yellow `.filter-active`
highlight) was left as in the reference, since only the blue was
project-specific branding.

Restructured `planting-locations/index.blade.php`'s filter form to use this
new class family, added `<label>`s for Division/Search/Sort (previously
unlabeled, centered-text inputs), and a Clear button that's visually
disabled (`.filter-btn-disabled`, no `href`) when no filter is active vs.
`.filter-btn-secondary-active` when one is — matching the reference's
active/disabled distinction, which the old plain-gray "Reset" link didn't
have. No backend change: still the same `division`/`search`/`sort` query
params, same `PlantingLocationController::index()` logic.

**Explicitly not done in this task**

- No other icon in the app was touched — sidebar hamburger/close/chevron
  stay inline SVG, emoji icons elsewhere (📏🌰🤝📍 etc.) stay as emoji.
  Broader FontAwesome adoption for menu items/headings is a separate,
  later task.
- `home.blade.php`'s existing `fas fa-phone` / `fas fa-envelope` markup
  (`home.blade.php:220-221,246-247`) was not touched — it will simply start
  rendering correctly now that FontAwesome is actually loaded app-wide, as
  a side effect of this change rather than a deliberate edit.

## 2026-08-26 — Replaced top nav bar with a persistent sidebar (desktop) + left-sliding drawer (mobile)

**Context**

Follows a dedicated investigation pass (full nav item inventory, Tailwind
theme audit, z-index audit, width-cap audit) covering `layouts/app.blade.php`
and `layouts/navigation.blade.php`. Findings that shaped the decisions below:
the app had no fixed-header-offset (`pt-16`-style) hacks to unwind since the
old nav was static-flow, not fixed; 9 pages independently cap their own
content at `max-w-7xl`; nothing in the app exceeded `z-50`; the `sm:` (640px)
breakpoint was the only mobile/desktop switch point in the old nav; and two
separate, drifted implementations of the profile/logout UI existed (a desktop
dropdown, an always-expanded mobile block).

**Decision**

- `layouts/app.blade.php`: body becomes `sm:flex` — hero banner
  (`partials/header.blade.php`) stays untouched, full-width, above the flex
  row (not inside it, not touched at all). The sidebar and a `flex-1 min-w-0`
  content column (still containing the original `$header`/`<main>` slot
  markup, byte-for-byte) sit side by side at `sm:`+. Below `sm:`, `sm:flex`
  doesn't apply, so the layout stacks vertically as before.
- Nav data now lives in one place, `layouts/partials/nav-items.blade.php`,
  rendered twice — once for the sidebar (new `<x-sidebar-link>` component,
  vertical, `primary`-colored active state), once for the mobile drawer
  (existing `<x-responsive-nav-link>`, reused as-is) — via an
  `<x-dynamic-component>` keyed on a `$linkComponent` param passed at each
  `@include`. This guarantees the sidebar and drawer can never drift out of
  sync on items, order, or `@role` gating again, which the old
  copy-pasted-twice `navigation.blade.php` was already prone to (desktop and
  mobile orderings had quietly diverged — "Tree Types" was in a different
  position on each). All 10 nav items, their route names, and their exact
  `@role`/`@auth` gates are unchanged from before; no items added or removed.
- Profile/role/logout consolidated into one partial,
  `layouts/partials/user-menu.blade.php`, included by both the sidebar and
  the drawer — replacing the two previously-separate implementations. Same
  logout mechanism as before (hidden form + `onclick` JS submit), just one
  copy of it instead of two. No avatar/profile picture added — out of scope,
  matches prior behavior (name + role text only).
- `responsive-nav-link.blade.php`'s active-state color changed from Tailwind
  `indigo` to the theme's `primary` (`#2F855A`, `tailwind.config.js`), so
  active-state styling is consistent between the sidebar and the drawer and
  no new arbitrary color is introduced. Safe to change in place: confirmed
  via full-codebase grep that `x-responsive-nav-link` (and `x-nav-link`) were
  used nowhere except the old `navigation.blade.php`.
- Mobile drawer/backdrop use `z-40` (investigation confirmed nothing in the
  app currently exceeds `z-50`, so this sits below existing modals/dropdowns
  while still layering above ordinary page content). Clicking a nav link
  inside the open drawer also closes it (`@click="open = false"` on the
  drawer's `<nav>` wrapper) — not explicitly requested, but without it the
  drawer would stay open after navigating, which is a usability bug for any
  off-canvas pattern, not a new feature.
- The 640px (`sm:`) breakpoint is unchanged — same cutoff as the old nav,
  deliberately not revisited here.
- The 9 pages that independently cap their own content at `max-w-7xl`
  (`home`, `dashboard`, `stats/map`, `users/report`, `profile/edit`,
  `stats/stats1`, `tree-plantings/report`, plus the layout/nav files
  themselves) are untouched internally — the sidebar just sits to the left
  of whatever width they already render at. None of them were widened to use
  the freed-up horizontal space; that's a separate decision for later if
  wanted, not bundled into this change.

**Known, deliberately not touched as part of this change**

- `x-nav-link` (`components/nav-link.blade.php`) is now unused — it was the
  old horizontal top-bar link style, which no longer applies anywhere now
  that both the sidebar and drawer are vertical. Left in place rather than
  deleted, since removing unused files wasn't part of this task's scope.
- The leftover `data-theme="emerald"` attribute on `<html>` in
  `layouts/app.blade.php` (from a removed DaisyUI dependency) has no effect
  and was left as-is.
- `pictures/create.blade.php`'s redundant nested `min-h-screen` wrapper
  (inside the already-`min-h-screen` layout) was left as-is.
- No collapsible/icon-only sidebar mode — a simple, always-fully-expanded
  fixed-width (`w-64`) sidebar was judged sufficient; a collapse toggle can
  be added later as its own decision if the sidebar proves too wide for any
  workflow.

**Reasoning**

A single persistent sidebar reads better than a horizontal bar once the nav
item count reaches 10 (plus profile/logout), especially on the map-heavy
pages this app centers on, and it removes the desktop-only dropdown that was
duplicating what the mobile menu already did inline. Consolidating the two
nav-rendering paths into one data source removes an entire class of "desktop
and mobile silently went out of sync" bugs that had already happened once
(the Tree Types ordering drift) before this change.

## 2026-08-22 — Removed "Move Plantings" button from planting-locations show page

Removed the "Move Plantings" link from `show.blade.php` (was lines 95-99) —
the button and its `@role('Admin|SuperAdmin')` gate only, nothing else.
The underlying feature is unchanged and fully intact:

- Routes: `planting-locations.move-form` (GET) and `planting-locations.move`
  (POST), `routes/web.php:71-75`, still role-gated (`auth`,
  `role:Admin|SuperAdmin`) and reachable by direct URL.
- Controller: `PlantingLocationController::moveForm()` / `::executeMove()`,
  including the `ChangeLogger`-based audit trail (action `'moved'`) — this
  is the same audit-trail integration documented earlier in this file as
  part of the Phase 1 MRV design; unaffected by this change.
- View: `move.blade.php`, unchanged.
- Tests: `tests/Feature/ChangeLogTest.php:67-101`, hit the route directly
  and don't depend on the button's markup; still pass.

Reason: the move-plantings workflow wasn't proving necessary in day-to-day
use, and the button was adding clutter to the show page's action row.
Rather than delete the feature (routes, controller, audit logging, tests),
it's kept intact and reachable by direct URL for an Admin/SuperAdmin who
needs it — just no longer surfaced as a button. If it turns out to be
unused entirely going forward, a future decision can revisit whether to
remove it properly (route, controller, view, tests) rather than just hide it.

## 2026-08-22 — Adjacent PlantingLocations on the show/edit maps (standalone improvement, not a roadmap phase)

**Context**

No proximity/nearby-location query existed anywhere in this codebase
before this (confirmed during the Phase 5 investigation) — this is new
logic, not wiring up something partial. The goal is purely visual
orientation: when viewing or editing one location's map, see other
locations nearby as dimmed context, without leaving the page.

**Decision**

- **Bounding box, not true circular distance.** "Adjacent" means falling
  inside a plain latitude/longitude bounding box sized to roughly a
  configured radius (default 10km) — not Haversine/great-circle distance,
  and not any spatial database feature. Same reasoning as Phase 5's
  choice to keep `boundary_geojson` a plain JSON column rather than adopt
  spatial storage: nothing in this app has a confirmed need for precise
  distance math, and a bounding box is enough for "show me what's roughly
  nearby on a map I'm already looking at." The radius
  (`NearbyLocationFinder::DEFAULT_RADIUS_KM`) and the result cap
  (`NearbyLocationFinder::DEFAULT_LIMIT`, 50 — a defensive limit against a
  dense cluster producing an unbounded response) are named class
  constants, not magic numbers buried in the query, specifically so
  they're easy to find and change later.
- **Longitude delta accounts for latitude, guarded near the poles.**
  Longitude degrees cover less ground distance further from the equator,
  so the bounding box's longitude half-width is divided by
  `cos(latitude in radians)`. That cosine is clamped away from zero
  before dividing — not because this app's real-world data (Nigeria,
  Kenya, and similar) will ever be anywhere near the poles, but because
  an unguarded division there would silently blow up rather than fail
  predictably, and guarding it costs nothing.
- **Admin-only scope: `planting-locations/show.blade.php` (via
  `map2.blade.php`'s new `:neighbors` prop) and
  `planting-locations/edit.blade.php` (its own separate inline Leaflet
  init) only.** Not `create.blade.php` (no existing location to be
  "adjacent" to yet), and deliberately not the public page —
  `PublicPlantingLocationController` was not touched, so this data is
  simply never built or passed there, the same enforcement pattern
  already established for the photo-consent-checkbox admin/public split.
- **Minimal data per neighbor: id, name, latitude, longitude,
  boundary_geojson — nothing else.** No status, division, comment, or
  contributor data crosses into the neighbor list, even though all of it
  is already visible elsewhere to the same admin viewer — the point is
  "here's roughly what else is nearby," not a second copy of that
  location's own detail page.
- **Visually distinct from the current location's own styling either
  way it currently looks.** The current location's own boundary style
  was changed by a separate, earlier task the same day (from a dark
  green to `#3388ff`, matching the edit page's Leaflet.draw color) — this
  work re-read `map2.blade.php`'s actual current state rather than
  assuming the color from memory, and picked a gray
  (`#9ca3af`, thin weight, low/no fill) that's distinct from that blue,
  from the green "this location" marker, and from the red/violet photo
  markers layered on the same map. Neighbor markers reuse the existing
  `leaflet-color-markers` grey variant — no new asset. Popups are just
  the neighbor's name, linked to its own admin show page.
- **`edit.blade.php` duplicates map2's neighbor-rendering logic rather
  than sharing it**, consistent with how this file already duplicates
  (rather than reuses) map2's OSM/satellite-toggle setup from the
  earlier Mapbox task — this file has never shared Leaflet
  initialization with the `map2` component, and this doesn't change that
  existing pattern.

**Reasoning**

Like the two entries immediately above this one, this is scoped as a
standalone, additive UI improvement rather than a roadmap phase — no
schema change, and nothing about an existing location's own data or
rendering is touched. The bounding-box approach mirrors Phase 5's own
precedent for not reaching for spatial-database machinery until a real
need for precise distance math actually exists.

---

## 2026-08-22 — Photo capture-location markers now public on both pages, plus an upload-time consent attestation

**Context**

This revises the decision recorded immediately below ("Photo
capture-location markers + boundary flagging on the admin map"), made
earlier the same day. After discussion, the scope changed: photo
location markers are now shown on **both** the admin planting-location
show page and the public `/p/{public_code}` page, not admin-only. This
entry records that as an explicit, deliberate reversal — not a silent
overwrite of the earlier call.

**Decision: reverse Phase 4's admin-only stance for captured photo
coordinates specifically, and add a consent mitigation that didn't
exist when that stance was made**

- **What's actually being reversed.** Phase 4 decided captured photo
  coordinates (`captured_latitude`/`captured_longitude`) were an
  incidental disclosure — unlike a project site's own coordinates, which
  a contributor deliberately shares, a photo's location could
  accidentally be a contributor's own home — and kept them out of every
  public view. This reverses *that specific display-scope decision*,
  and only that: Phase 4's EXIF extraction, device-geolocation capture,
  and storage are completely unchanged. The data was always being
  captured; what changes is where it's shown.
- **Why the reversal is justified now, not just convenient.** Two things
  are different from when Phase 4 made its original call: (1) visibility
  of on-the-ground project activity is core to what this app is for —
  the whole point of `/p/{public_code}` is letting an outside party see
  real evidence a planting happened where it's claimed to have happened,
  and photo location is exactly that kind of evidence; (2) admin already
  reviews and moderates every photo before it stays up (delete/
  toggle-welcome are both already-existing admin actions on every
  photo), so there's an existing human review step this can lean on,
  which is a materially different situation than "arbitrary uploads go
  instantly and irreversibly public with zero review."
- **A required consent attestation checkbox is the actual mitigation for
  the risk Phase 4 originally flagged** — not a technical fix (no face
  detection or blurring; out of scope, this is a human-judgment
  approach), but a point where the uploader has to affirmatively assert
  the photo doesn't show identifiable people, or that anyone shown has
  consented to appear in public materials. `pictures.consent_confirmed_at`
  is a new nullable timestamp, set from the request the moment the
  checkbox is validated `accepted` — required on both the camera-capture
  path (`PictureController::store`) and the file-upload path
  (`PictureController::uploadStore`, one shared timestamp per batch,
  since one checkbox covers the whole batch as one attestation act).
  **Deliberately not backfilled or retroactively required**: existing
  photos keep `consent_confirmed_at = null` forever and continue to
  display and behave exactly as before — this is a going-forward
  practice, not a retroactive gate that would either block legitimate
  existing content or require fabricating an attestation that never
  actually happened.
- **A short, static moderation reminder was added to the admin show page
  near the photos section** ("Review uploaded photos regularly. Remove
  any that are irrelevant, inappropriate, or show identifiable
  individuals without confirmed consent.") — deliberately separate from
  the upload-time checkbox. The checkbox is a one-time attestation by
  the uploader at the moment of upload; the reminder is an ongoing
  prompt for whoever's reviewing the gallery later, since the checkbox
  by itself doesn't guarantee an uploader's judgment was correct.
- **Jurisdiction-agnostic language, deliberately.** This app is already
  used across multiple African countries, each with its own data
  protection framework and its own specific rules (e.g. around
  biometric/facial data, or parental consent for minors). No UI text,
  validation message, or code comment names a specific country's law —
  the checkbox label and every related string state the underlying
  principle (identifiable individuals need to have consented) without
  claiming compliance with, or citing, any one jurisdiction's statute.
  This app has no country-detection or jurisdiction-specific logic, and
  this feature doesn't add any.
- **Shared `PhotoLocationMarkerService`, not duplicated logic.** Both
  `PlantingLocationController::show` and
  `PublicPlantingLocationController::show` now need the identical
  annotated-photo-list construction (filter to photos with captured
  coordinates, compute the point-in-polygon inside/outside/null flag,
  build the popup fields), so that logic moved into one service both
  controllers call, rather than copy-pasting the same mapping in two
  places.
- **Point-in-polygon (`app/Services/PointInPolygon.php`) is unchanged
  from the original design**: server-side PHP ray-casting against the
  outer ring only (this app's boundary_geojson never has holes or
  multiple rings), null — never a default false — when there's no
  boundary to check against, and PHP rather than client-side JS
  specifically because this codebase has no JS test infrastructure and
  a PHP service can be unit-tested directly, including against a
  concave polygon shape that actually exercises ray-casting rather than
  a bounding-box approximation.
- **Two marker colors, unchanged**: red (reusing the existing
  `leaflet-color-markers` icon set) for a photo inside the boundary or
  where there's no boundary to check against; violet for one flagged
  outside — visible across the whole map at a glance, not just after
  clicking each marker.

**Test suite note**

The feature test that previously asserted the public page never exposes
this data was replaced, not deleted silently — `tests/Feature/PhotoLocationBoundaryTest.php`
carries an explicit comment at that point in the file explaining the
assertion was correct for the *previous* version of this decision and
has been intentionally inverted, so a future reader diffing test history
doesn't mistake it for an uncaught privacy regression.

**Reasoning**

The core judgment call here is that "visible, but with a real consent
step and existing human review" is a better tradeoff for this app's
actual purpose than "hidden everywhere, with no mitigation because the
data was never going to be shown." Recording this as an explicit
reversal — rather than silently treating the current scope as if it
were always the plan — keeps this log doing its job: a future reader
should be able to see that Phase 4 made one call, and this decision
deliberately made a different one for a stated reason, not that the
project simply drifted.

---

## 2026-08-22 — Photo capture-location markers + boundary flagging on the admin map (standalone improvement, not a roadmap phase)

**Context**

Builds on two existing pieces without changing either: Phase 4's
`Picture.captured_latitude`/`captured_longitude`/`captured_at`/
`capture_source` (captured but, until now, never surfaced anywhere) and
Phase 5's `PlantingLocation.boundary_geojson`. The goal is to make it
visible at a glance, on the admin map, when a photo's captured location
doesn't actually fall within the site's mapped boundary — e.g. a photo
uploaded from off-site, or a location whose EXIF/GPS is unreliable.

**Decision: admin-only, enforced by never building the data for the public path**

- **This extends Phase 4's own privacy rationale, not a new judgment
  call.** Phase 4 already decided captured photo coordinates are an
  incidental disclosure (could be a contributor's own home) and kept
  them out of every public view. Rendering them as map markers is a much
  more visible form of exposure than a hidden column ever was, so the
  same boundary applies with more force, not less.
- **The enforcement point is `PlantingLocationController::show()`
  building the annotated `$photos` list and passing it to `<x-map2>`
  — `PublicPlantingLocationController::show()` was read, confirmed, and
  left completely untouched.** `map2.blade.php`'s new marker/popup block
  is gated on `isset($photos) && count($photos)`, so the public page
  (which never sets that prop) renders exactly as it did before this
  change — not a hidden flag that could be flipped, but data that
  genuinely never leaves the admin controller. A feature test asserts
  the public page's own view data has no `photos` key at all, and that
  the rendered HTML contains neither the raw captured coordinates nor
  any of `captured_latitude`/`captured_longitude`/`inside_boundary`/the
  warning copy — a regression guard against this being added to the
  public view by mistake later.
- **Point-in-polygon runs server-side in PHP (`app/Services/PointInPolygon.php`),
  not client-side JS.** This codebase has no JS test infrastructure
  (confirmed again during the Mapbox toggle work) — a PHP service is
  directly unit-testable with Pest, including against a deliberately
  non-rectangular (concave, L-shaped) polygon to actually exercise
  ray-casting rather than a simpler bounding-box approximation a
  rectangle-only test could pass by accident. It also means the
  inside/outside flag ships to the browser as a plain boolean already
  computed, not geometry the client has to re-derive.
- **`null` means "no boundary to check against," never a default
  `false`.** A location with no `boundary_geojson` yet has nothing to
  violate — flagging every one of its photos "outside" would be a false
  problem signal with no basis, so the flag is only ever computed when
  `$plantingLocation->boundary_geojson !== null`.
- **Two marker colors, not one plus a popup-only warning.** Red (reusing
  the same `leaflet-color-markers` icon set already loaded for the
  existing green location marker — no new asset) for a photo inside the
  boundary, or where there's no boundary at all to check against; violet
  for one flagged outside. The point is a problem being visible across
  the whole map at a glance — a popup-only warning would require
  clicking every marker individually to find it, which defeats the
  purpose for a location with many photos.

**Reasoning**

This is scoped as a standalone read-only view over two phases' existing
data, not a new phase — no schema change, no change to EXIF extraction
or boundary editing. The only genuinely consequential decision is the
privacy one, and it's deliberately enforced at the one place it actually
matters (the public controller never building the data), verified by a
test that would fail if that guarantee were ever broken.

---

## 2026-08-22 — Mapbox satellite imagery toggle (standalone improvement, not a roadmap phase)

**Context**

Not part of the numbered MRV roadmap — a UI/tooling improvement so a
boundary polygon (Phase 5's Leaflet.draw feature, on the create/edit
forms) can be drawn against visible terrain/tree cover instead of a
blank OpenStreetMap street layer. Also available on the single-location
map used by the admin show page and the public `/p/{public_code}` page
(`map2.blade.php`). Deliberately not extended to the multi-marker
overview map (`map.blade.php`, homepage/stats) — that can be done later
the same way if wanted.

**Decision: Mapbox raster tiles via plain `L.tileLayer`, token-gated, no new dependency**

- **Mapbox over Esri or Google.** Esri's terms/free-tier imagery access
  are currently in flux/being migrated, making them an unstable base to
  build on. Raw Google satellite tiles are prohibited by Google's ToS
  without their full JS API plus billing — a materially bigger
  integration than a single tile layer. Mapbox's raster tile terms are
  clear and stable, and (per the investigation before implementing)
  works through a plain `L.tileLayer` URL exactly like the existing OSM
  layer — no new JS package (no `mapbox-gl`) needed.
- **`satellite-streets-v12`, not plain `satellite-v9`** — imagery with a
  road/label overlay, meaningfully more usable for on-the-ground site
  work than unlabeled imagery alone.
- **Graceful no-token fallback is the default, not an edge case.** No
  `MAPBOX_ACCESS_TOKEN` existed in this project before this change.
  `window.mapboxAccessToken` (set once from `config('services.mapbox.access_token')`)
  gates both the satellite tile layer's construction and the
  `L.control.layers` toggle — with no token, nothing beyond today's
  OSM-only behavior is added: no empty control, no console error.
- **Token exposure had to be adapted from the original single-injection
  plan.** The plan was to inject `window.mapboxAccessToken` once in
  `layouts/app.blade.php`, since Leaflet is already loaded there. That
  works for the admin show page and the create/edit forms, but
  `public/planting-locations/show.blade.php` (the `/p/{public_code}`
  page) turned out to be a **standalone HTML document that does not
  extend `layouts/app.blade.php`** — it loads Leaflet itself via
  `map2.blade.php`'s own `@once` block. The token is therefore also set
  inside that same `@once` block, so the public page is self-sufficient;
  on the admin show page this runs redundantly alongside the layout's
  own injection, which is harmless since both set the same value.
- **Scope: `map2.blade.php` + create/edit forms only**, matching each
  file's existing independent Leaflet init (create/edit still don't
  reuse `map2.blade.php`, per the pre-existing structure flagged in the
  Phase 5 investigation). The homepage/stats overview map
  (`map.blade.php`) is untouched. The pre-existing redundant Leaflet CDN
  loading across multiple files (also flagged in Phase 5) is unrelated
  to this change and was not touched.

**Reasoning**

This is scoped as a standalone, additive UI change rather than a
roadmap phase because it changes nothing about the data model or
storage — it only gives the existing Phase 5 boundary-drawing tool a
better visual reference to draw against, config-gated so its absence
(no token) reproduces today's behavior exactly.

---

## 2026-08-21 — Phase 7: public GeoJSON/API export layer — investigated, deliberately deferred

**Context**

Phase 7 (the roadmap's final phase: a read-only public API/export layer
exposing cohorts, measurements, and boundaries so an independent party
can pull raw data and verify it directly) was investigated in full —
current public exposure via `/p/{public_code}`, the deliberate
non-exposure decisions made by Phases 1-6, existing API/routing
infrastructure, rate limiting, pagination patterns, and data-shape
options. No code changed as part of this investigation.

**Decision: defer implementation**

Every prior phase (1-6) changed internal app behavior only — a wrong
call was fixable by another internal PR at any time, at zero external
coordination cost. A public API is qualitatively different: once
external tooling depends on a JSON shape, changing that shape breaks
someone else's integration, with no way to even know who's affected.
Building this speculatively, before a real external consumer exists to
shape it against, risks guessing wrong on a decision that's expensive to
reverse. Better to build it when an actual external party — an auditor,
a certification body, a specific integration need — is asking for it,
so the shape is driven by a real requirement instead of a guess.

**Key findings preserved for whenever this is picked back up**

1. **Non-exposure checklist, to re-check at implementation time:**
   - `Picture.captured_latitude`/`captured_longitude`/`capture_source`
     must **never** be exposed — Phase 4's documented privacy rationale
     still applies (a photo's embedded location can be a contributor's
     own home, not the project site).
   - Individual staff/volunteer identity —
     `TreePlantingMeasurement.verified_by_user_id`, any other `*.user_id`
     field, `TreePlanting.status_updated_by` — has never been made
     public anywhere in this app and needs its own deliberate decision,
     not a default. This is a different kind of disclosure than
     `Contributor.name` (an organization's identity, already
     deliberately made public by Phase 6). If measurement verification
     status is ever exposed, the verified boolean and the identity of
     who verified it are separable questions — the boolean carries the
     credibility signal Phase 7 exists to provide; the name doesn't need
     to come with it.
   - Already safe to expose as JSON, because it's already fully public
     today on `/p/{public_code}`, just not machine-readable: `TreeType`
     reference fields, `boundary_geojson`, `Contributor` name/website,
     planting counts/dates/species, location lat/lng.
   - Not currently public at all — a genuinely new disclosure if a
     future implementation includes it: `TreePlantingMeasurement` data
     (survival/growth records) and biochar batch data. Neither has ever
     reached a public-facing view.
2. **No existing API scaffolding of any kind** — no `routes/api.php`, no
   API middleware group registered in `bootstrap/app.php`, no
   Sanctum or other API-auth package installed, no versioning convention
   anywhere in the codebase.
3. **No rate limiting exists on any public route today** —
   `/p/{public_code}` can currently be hit at unlimited rate. This gap
   predates Phase 7 and is independent of it, but a public bulk-export
   API raises the stakes on fixing it.
4. **Pagination is the only pattern used anywhere in this app** — every
   list-producing controller paginates; there is zero precedent for an
   unbounded "everything" response. At current scale a single
   unpaginated response is borderline defensible, but introducing
   pagination later as a breaking change to a live external contract is
   worse than building it in from day one.
5. **Two distinct likely consumers need genuinely different shapes** — a
   GIS auditor wants one clean `PlantingLocation`-rooted GeoJSON
   `FeatureCollection` (boundary as geometry, everything else nested in
   `properties`); a data-analyst auditor wants flat, cross-location
   tabular access (e.g. every `TreePlantingMeasurement` platform-wide)
   that a nested-per-location response makes awkward. "One combined
   endpoint" was not resolved as sufficient for both use cases — this is
   an open question for the eventual implementation discussion, not
   something this investigation settled.
6. **If/when built: version from day one** (e.g. `/v1/...`), in a
   separate `Api\` namespace rather than added onto
   `PublicPlantingLocationController` (which serves Blade views — a
   different response contract and different concerns). Low cost to do
   now, expensive to retrofit onto a live external contract later.

**Status**

Not started. Revisit when a concrete external consumer or requirement —
an auditor, a certification body, a specific integration request —
makes the actual shape and access needs concrete, rather than building
speculatively.

---

## 2026-08-19 — Phase 6: structured Contributor model, attached to TreePlanting

**Context**

Phase 6 replaces the unstructured funder/partner story with a real
`Contributor` entity — but attached at a different level than the
original roadmap framing assumed. The roadmap's one-line description
("Replace the free-text contributor blob with a structured
Contributor/Organization model + pivot to planting events") already said
"planting events," but the Phase 6 investigation's own framing leaned
toward `PlantingLocation`-level attachment as the default reading. The
actual decision made here is a **correction**: contributors attach to a
specific `TreePlanting` (many-to-many), not to the `PlantingLocation` as
a whole.

**Decision: attach to TreePlanting, not PlantingLocation**

A planting event is often a real-world gathering — a specific date, a
specific group of trees, often several organizations physically present
and involved in that one event. A site that has hosted multiple planting
days over its lifetime may have had entirely different organizations
involved each time (the March planting funded by one foundation, an
independently-organized June planting by a different partner). Attaching
contributors at the `PlantingLocation` level would flatten that history
into one undifferentiated site-wide list, unable to say which
organization was actually present at which event — exactly the kind of
information loss this roadmap has spent five phases eliminating
elsewhere (Phase 1's audit trail, Phase 2's cohort-linked measurements).
Attaching to `TreePlanting` instead keeps that distinction intact, and is
consistent with how this app already treats `TreePlanting` — not
`PlantingLocation` — as the unit multiple other structured records
attach to (`tree_planting_measurements` in Phase 2, `biochar_batches`'
nullable link in Phase 3).

**The legacy `PlantingLocation.contributors` field is frozen, not
migrated.** There is no reliable way to parse historical narrative HTML
prose into structured `Contributor` records — the investigation found no
sample content anywhere in the codebase to even attempt a parsing
strategy against, and free text written by a human for a human to read
is not the kind of input a deterministic migration script can safely
convert into discrete name/website/email fields without silently
inventing structure that was never really there. The column, its Quill.js
editor, its `strip_tags()` sanitization, and all three of its rendering
sites (public page, admin show page, and the dead
`planting-location-card.blade.php` component) are untouched by this
phase. It continues to serve exactly its current purpose: a general,
free-form site-level note. On the public page, its heading changed from
"Contributors" to "About This Site" with a one-line clarifying caption —
a minimal-friction relabel, not a restructuring — specifically so it
reads as clearly distinct from the new per-planting-event structured
section below it, rather than looking like a duplicate or a conflicting
source of truth for the same information.

**The pivot's place in the delete-behavior spectrum.** Across five
phases, delete behavior has been a deliberate, considered choice each
time, not a default:
- `change_logs` (Phase 1): unconstrained — an audit record outlives the
  row it describes.
- `tree_planting_measurements` (Phase 2): cascades — a measurement has
  no meaning independent of its planting.
- `biochar_batches` (Phase 3): `nullOnDelete` on both links — real
  material survives even if its planting-event link is removed.
- `contributor_tree_planting` (this phase): **cascades on the
  `tree_planting_id` side, restricts on the `contributor_id` side** —
  the first pivot in this spectrum with two different behaviors on its
  two foreign keys, because the two sides mean genuinely different
  things. Deleting a `TreePlanting` should take its attachment records
  with it (an attachment has no meaning independent of the event it
  documents, same reasoning as Phase 2's cascade). Deleting a
  `Contributor` while it's still attached to anything is blocked outright
  — same `RESTRICT` precedent already established by
  `TreeType`/`tree_type_id` on `tree_plantings` (`TreeTypeController::destroy`'s
  existing "cannot delete while in use" guard, mirrored exactly by
  `ContributorController::destroy`).

**`contributor_updated` vs. `contributor_attached`/`contributor_detached`
are deliberately separate `ChangeLog` concerns, not one.** Correcting a
contributor's own name or contact details ("we misspelled this
organization's name") is a different fact from an event-participation
change ("this organization was, or wasn't, actually involved in this
specific planting"). Conflating them into one log action would make it
impossible to later distinguish "the organization's identity was
corrected" from "the organization's involvement in this specific event
was added or removed" when reading the audit trail back. `contributor_updated`
lives on `Contributor` (`ContributorController::update()`, `BiocharBatch`-style
tracked-field diff); `contributor_attached`/`contributor_detached` live on
`TreePlanting` (`TreePlantingContributorController`), each carrying a
**name snapshot** at the moment of attachment/detachment specifically so
a later correction to the `Contributor`'s own name doesn't retroactively
change what an old attachment/detachment log entry appears to say.

**Narrower gate on the registry, broader gate on attach/detach.**
`ContributorController`'s own CRUD routes (creating, editing, deleting
`Contributor` records — managing the shared organization registry
itself) are gated `Admin|SuperAdmin` only. `TreePlantingContributorController`'s
attach/detach routes, and the `contributors.search` typeahead endpoint
they depend on, are gated to the broader `Admin|SuperAdmin|Monitor|Grower`
group already used for recording measurements and biochar batches.
The reasoning: correcting the shared registry affects every planting
event that organization is attached to across the whole platform, so it
warrants the narrower gate already used elsewhere for genuinely
platform-wide changes; attaching an existing, already-vetted contributor
to one specific event you're already permitted to edit is a much smaller
blast radius, matching the existing planting-event-level action pattern.

**Deliberately not built in this phase: cross-site aggregation.** No
"everything Organization X has funded across the whole platform" view or
endpoint exists after this phase. The investigation flagged this
specifically as a materially bigger public-disclosure decision than an
additive schema change — turning a collection of per-event facts into an
aggregated, queryable funder profile changes what's actually being
disclosed, even though each individual fact was already visible. That
decision belongs with Phase 7's deliberate public-API scope discussion,
if it happens at all, not as an incidental side effect of adding the
`Contributor` model here.

**Known pre-existing issue, not touched in this pass.** The investigation
found `resources/views/components/planting-location-card.blade.php` is
dead code (not included by any parent view) with a broken route
reference (`planting-locations.public.show`, which doesn't exist — the
real route is `public.planting-locations.show`). Left as-is, flagged
here for a future pass, same treatment as the dead `MapMarkerService`
filter flagged in Phase 1.

**Reasoning**

The core correction here — cohort-level attachment instead of
site-level — is the single most consequential decision in this phase,
and it came directly from recognizing that a planting event, not a
location, is the thing that actually has a "who was there" answer in the
real world. Every other decision in this entry (the freeze-don't-migrate
call, the split delete behavior, the split ChangeLog actions, the tiered
role gates) follows from treating `TreePlanting` as the right unit once
that correction was made, consistent with the pattern this roadmap has
followed since Phase 2.

---

## 2026-08-19 — Phase 5: site boundary geometry — platform decision and implementation

**Context**

This is the one genuine platform decision in the MRV roadmap (storage
engine / query capability), flagged as such from the start of the
roadmap rather than an additive schema change like Phases 3-4. A
dedicated investigation was run first to establish ground truth before
deciding anything (see the Phase 5 investigation output). Key findings
that drove this decision:

- Production's actual MySQL version is **not stated anywhere in this
  repository** — `CLAUDE.md` says only "MySQL on VPS," and
  `docs/deployment.md` is effectively an empty placeholder ("See notes
  Itacen_Mu Deployment") pointing at external notes not committed here.
  No `deploy.sh`, no CI pipeline, no GitHub Actions exist in this repo
  to infer it from either.
- Local dev, on the machine this work was done on, actually runs
  **MariaDB 10.4.32** via XAMPP (`.env` has `DB_CONNECTION=mysql`
  pointed at `127.0.0.1:3306`) — not SQLite, despite `.env.example`'s
  default and `CLAUDE.md`'s "SQLite used locally" claim. SQLite is
  test-suite-only (`phpunit.xml`'s `:memory:` override).
- The codebase is **genuinely greenfield** for this feature — no
  polygon/boundary/GeoJSON/WKT/geometry code, no Leaflet.draw or any
  drawing plugin, no shapefile/boundary data for Nigerian LGAs anywhere
  in `storage/`, `database/`, or `resources/`, and `Division` (despite
  its `LGA_name` implying real administrative boundaries exist
  externally) has only ever stored a centroid point, never attempted to
  ingest real boundary data.
- **No overlap/proximity/duplicate-detection logic exists anywhere in
  this app today**, and nothing in this project's own stated goals
  (across all four prior phases and the original roadmap) describes an
  in-app spatial query need (`ST_Intersects`-equivalent). What *is*
  explicitly stated is Phase 7's own description: a read-only export
  layer "so an independent party can pull raw data and verify it
  directly" — i.e., store-and-export for someone else's GIS tooling, not
  query-in-app.
- At the project's stated target scale (hundreds of thousands to
  low-millions of *trees*), the cohort model means `PlantingLocation`
  row count — the table a boundary would live on — is realistically in
  the **thousands to low tens of thousands**, not millions. A full table
  scan over that many geometry rows is not a performance problem any
  reasonable database struggles with.
- Every raw-SQL usage in the app (`DB::raw`/`whereRaw`/`selectRaw`, five
  call sites total) uses standard ANSI SQL (`MAX`, `SUM`, `CASE WHEN`) —
  nothing MySQL-specific that would block a future engine change if one
  were ever wanted.

**Decision**

**Boundaries are stored as a plain JSON column
(`planting_locations.boundary_geojson`, Laravel's `json()` type,
nullable), holding a single GeoJSON `Polygon` object — not native MySQL
spatial types, not PostGIS/PostgreSQL, no new database software of any
kind.** This works identically against the test suite's SQLite and this
machine's local MariaDB 10.4.32, and against whatever production's real
MySQL version turns out to be — a plain JSON column has no version floor
to worry about, unlike native spatial types.

Reasoning, weighed explicitly rather than defaulted into:
- The investigation found **no documented need for in-app spatial
  querying anywhere in this project** — the real, stated use case
  (Phase 7) is retrieval and export for external verification, which a
  plain JSON column serves exactly as well as a native geometry column
  would. Choosing PostGIS to serve a query capability nothing in this
  project has ever asked for would be solving a problem that doesn't
  exist yet, at real cost (introducing PostgreSQL as a new piece of
  infrastructure, an unconfirmed production migration, and permanent
  added operational complexity) against a benefit (spatial query
  performance) that doesn't apply at this scale even if the capability
  were needed.
- If a genuine in-app spatial query need materializes later — the
  investigation named the concrete example, overlap/duplicate-claim
  detection — that is a deliberate, visible, separately-justified
  decision to make *at that time*, informed by whether it's actually
  needed and what engine is actually available in production by then.
  This phase does not foreclose that option (the boundary data itself is
  captured either way); it just doesn't pay for spatial query capability
  today that has no current consumer.
- Production's actual database version being unconfirmed from this repo
  is itself a reason to prefer the option that doesn't depend on it. A
  plain JSON column works regardless of whether production turns out to
  be MySQL 5.6 or 8.0 (spatial types would need at least 5.7) — the
  decision doesn't require getting an answer to a question the codebase
  itself can't currently answer.

**Fingerprint, not full geometry, in `change_logs`**

Boundary edits are logged through `ChangeLogger` the same way coordinate
edits are (Phase 1's pattern), but `change_logs.old_values`/`new_values`
never contain the raw coordinates array — only a `GeometryFingerprint`
(`vertex_count`, `bounding_box`, and a deterministic `hash` of the
normalized coordinates). A hand-drawn or GPS-survey-derived boundary can
run to several KB as GeoJSON text; storing full before-and-after geometry
on every correction, in an unindexed JSON column with no size cap, is a
real bloat vector this phase deliberately avoids — the fingerprint is
enough to prove *that* a boundary changed and roughly *how much*, without
duplicating the entire shape into the audit trail on every edit. The
current, full-fidelity boundary is always available on the
`PlantingLocation` row itself; `change_logs` only needs to prove change
occurred, consistent with its role everywhere else in this app.

**Auto-close, don't reject, an unclosed ring**

`GeoJsonPolygonValidator` auto-closes an unclosed outer ring (appending a
copy of the first position) rather than rejecting the submission.
Leaflet.draw's raw `toGeoJSON()` output and any manually-constructed
GeoJSON can both plausibly omit the closing point — it's a trivially
fixable, well-defined correction, not a meaningful validation failure
worth punishing the user's submission over. The validator still rejects
what actually can't be salvaged: fewer than 3 distinct vertices (not a
polygon at all), non-numeric or out-of-range coordinates, and structural
GeoJSON shape violations (wrong `type`, missing `coordinates`).

**Single polygon, no holes, no multipolygons — this phase only**

`boundary_geojson` stores exactly one outer ring. If a site's real shape
later needs holes (e.g. an excluded inner area) or multiple disjoint
parts, that's a deliberate, separately-scoped future extension — this
phase does not build speculative support for shapes no current site
actually needs, consistent with how every prior phase in this roadmap
has scoped itself to the concrete, present need rather than a
hypothetical future one.

**Reasoning**

This is the most consequential decision in the roadmap so far because
it's the one genuine platform choice rather than an additive schema
change — and the investigation's job was to make sure that choice got
made on real evidence (actual codebase state, actual stated use case,
actual scale) rather than on the assumption that "boundaries" implies
"spatial database." It didn't, here, and the decision reflects that.

---

## 2026-08-19 — Phase 4: GPS capture provenance, photo EXIF/geolocation provenance

**Context**

Phase 4 targets two related gaps the original audit flagged: GPS capture
method and accuracy (`position.coords.accuracy`) were available from the
browser's Geolocation API but never used anywhere in the codebase, and
uploaded photos had no provenance data independent of the file itself. The
Phase 4 investigation corrected two assumptions along the way worth
recording here: there is no map-drag coordinate picker (the Leaflet map
is a read-only preview, not an input device — only manual typing and the
GPS button are real capture methods), and `position.coords.accuracy` was
never read at all, not merely read-and-discarded.

**Decision**

- **GPS capture-method/accuracy lives in `change_logs`, not as columns on
  `planting_locations`.** Capture method and accuracy describe *the act
  of setting coordinates on one specific occasion*, not a durable
  property of the location. If they lived on `planting_locations`
  directly, the next coordinate edit — by any method — would silently
  overwrite the previous method's record, which is exactly the kind of
  loss Phase 1 exists to prevent. Explicitly out of scope for this
  phase: no denormalized "current capture method" column on
  `planting_locations` either — a future "GPS-verified" badge, if
  wanted, should be computed from the latest relevant `change_logs` row,
  not stored separately and risk drifting out of sync with the log that
  is the actual source of truth.
- **`PlantingLocationController::store()` now writes its own
  `coordinates_set` entry** — Phase 1 only instrumented `update()`,
  which meant a location's very first coordinate-setting (the *only*
  one that happens for most locations, since most locations are never
  re-pinned) went completely unlogged. This was a real gap, not a
  deliberate omission: it's fixed here by wrapping `create()` +
  the conditional `ChangeLogger::record()` call in `DB::transaction()`,
  matching every other phase's atomicity rule, and skipping the log
  entirely when neither latitude nor longitude was provided (no
  coordinate event actually happened). The existing `coordinates_updated`
  entry in `update()` gains `capture_method`/`accuracy_meters` in its
  `new_values` only — there's no meaningful "old capture method" to
  diff against, since this describes the act of the edit, not a prior
  durable state.
- **Photo EXIF data is plain columns directly on `pictures`**
  (`captured_at`, `captured_latitude`, `captured_longitude`,
  `capture_source`), not routed through `change_logs`, for the opposite
  reason from the GPS decision above: EXIF data isn't describing an edit
  to anything, it's an intrinsic property of the file, extracted once at
  upload time. There's no before/after to diff — photos in this app
  aren't edited in place, only deleted or toggled on `show_on_welcome`.
  This is consistent with how `Picture` already stores other file-intrinsic
  metadata (`path`, `thumbnail`) as plain columns.
- **Two structurally different provenance mechanisms for the two photo
  paths, because they're structurally different problems**:
  - The file-upload path (`uploadStore()`) gets real files with
    (possibly) real embedded EXIF, so a new `ExifExtractor` service
    parses it after the file is stored — a thin wrapper around PHP's
    native `exif_read_data()`, deliberately *not* `intervention/image`,
    since the Phase 4 investigation confirmed that package's own EXIF
    support is just this same native function under the hood; pulling
    in the full decode pipeline for this alone wasn't justified.
    `capture_source` is only set to `'exif'` when the extractor actually
    finds something usable — a read that comes back empty leaves all
    four fields null, not `'exif'` with nulls, so a null `capture_source`
    reliably means "we don't know," never "we checked and it was empty."
  - The canvas camera-capture path (`store()`) produces a
    `canvas.toDataURL()` JPEG, which — per the investigation — has no
    embedded EXIF by construction; the Canvas 2D API has no mechanism to
    write it. There is nothing to extract server-side, so provenance
    here has to come from a live `navigator.geolocation.getCurrentPosition()`
    call fired client-side at the moment of capture, submitted as two
    plain hidden fields (`captured_latitude`/`captured_longitude`).
    `capture_source` is `'device_geolocation'` when both are present,
    and `captured_at` is set to `now()` server-side, since a canvas
    capture has no independent embedded timestamp — server receipt time
    is the closest available signal, not a substitute for a real EXIF
    timestamp.
- **Captured photo coordinates are admin-only for this phase — not
  public**, a deliberate contrast with Phase 3's `TreeType` fields, which
  inherited the existing all-public default because nothing gated
  `TreeType` from public view and there was no privacy reason to add a
  gate. This case is different: a planting location's own coordinates
  are a scoped, deliberate disclosure a contributor understands they're
  making about a project site. A photo's embedded EXIF or
  device-geolocation coordinates are an incidental disclosure — the
  photo could be taken from, or near, a contributor's own home, and they
  may not think about that when uploading a picture of a sapling.
  `captured_latitude`/`captured_longitude`/`capture_source` are not
  added to `/p/{public_code}` or any other public view in this phase.
  Revisiting this later is a deliberate, separate decision, not a
  default to fall into.
- **EXIF extraction runs synchronously, inline, inside the upload
  request** — no queued job. `ExifExtractor::extract()` never throws
  (wrapped in try/catch internally, plus a `function_exists('exif_read_data')`
  guard) and always degrades to all-null fields rather than failing the
  upload, so the risk this decision accepts is added *latency* per
  upload, not added *failure modes*. This is the first thing to revisit
  if upload volume makes synchronous EXIF parsing a bottleneck — this
  codebase already runs a queue worker as part of normal local dev
  (`composer.json`'s `dev` script includes `queue:listen`), so moving
  extraction into a queued job later that patches the `Picture` row
  after the fact is a contained, low-risk follow-up, not new
  infrastructure.
- **Frontend capture-method tracking uses a module-level guard flag**
  (`settingViaGps`) in both `create.blade.php` and `edit.blade.php`'s
  JS, set true immediately before `getLocation()` writes `.value` on the
  latitude/longitude fields and reset false immediately after, so the
  shared `input` event listener can tell "the GPS button just set this"
  apart from "the user just typed a correction" and revert
  `capture_method` to `manual` only in the latter case. One technical
  note worth recording: in standard DOM behavior, assigning
  `element.value = ...` from JavaScript does **not** itself fire an
  `input` event (only genuine user interaction, or an explicit
  `dispatchEvent`, does) — so in practice the guard flag's "true" branch
  is defensively unreachable under normal browser behavior today, since
  `getLocation()` already calls `updateMapMarker()` directly rather than
  relying on the listener to catch its own assignment. The flag is
  implemented anyway, as specified, since it's harmless and guards
  against any future code path (an autofill extension, a different
  future input mechanism) that might genuinely dispatch that event.

**Reasoning**

Both mechanisms follow directly from the investigation's own read,
adopted rather than re-derived: an event's metadata belongs where the
event is already logged (Phase 1's `change_logs`), and a file's intrinsic
property belongs where the file's other intrinsic properties already
live (`pictures`' own columns). Keeping captured-photo coordinates out of
the public view, while `TreeType`'s reference fields went public by
default in Phase 3, is the first place in this roadmap where "public by
default" was deliberately *not* the answer — worth remembering as
precedent the next time a new field's public/private status isn't
obvious from what came before it.

---

## 2026-08-19 — Phase 3: biochar_batches entity, TreeType carbon reference fields

**Context**

Phase 3 targets two additive gaps from the original audit. Biochar was a
single `decimal(4,2)` column directly on `tree_plantings`, constrained to
four dropdown values labeled by tree-size category ("Minimal — 0.25 kg
dry," "Large tree — 2.0 kg dry"). The Phase 3 investigation found this
column is actually a **per-tree rate**, not a total quantity — the
homepage stat computes `SUM(number_of_trees * biochar)` to arrive at a
total, which only works because of that rate design. `TreeType` had zero
fields beyond `name`/`latin_name`/`description`, and the investigation
confirmed everything on it is already fully public on `/p/{public_code}`
with no gating mechanism to hold new fields back if that's ever wanted.

**Decision**

- **`biochar_batches` is a new, independently-locatable table**, not a
  restructuring of the old column. `quantity_kg` is an actual total
  applied, not a rate — the semantic fix the investigation flagged as
  necessary. A batch links via nullable `planting_location_id` and
  nullable `tree_planting_id`; the controller requires at least one to be
  present (a batch must be locatable somewhere), and when `tree_planting_id`
  is given, `planting_location_id` is derived from it server-side rather
  than trusted from the request, so the two can never contradict each
  other.
- **The old `tree_plantings.biochar` column is frozen, not migrated.**
  No backfill, no conversion into `biochar_batches` rows, no schema
  change to the column itself. It simply stops being written to: the
  dropdown is removed from the tree-planting create/edit form, and (a
  deliberate small extension beyond the literal Phase 3 brief, flagged
  here rather than done silently) the `biochar` validation rule was also
  removed from `TreePlantingController::store`/`update`, so the backend
  can no longer accept a write to it either — leaving the rule in place
  after removing its only UI entry point would have been the same kind
  of unused-acceptance-surface landmine the Phase 2 review caught with
  `verified_by_user_id` in `$fillable`. Historical display of old values
  (`planting-locations/show.blade.php`'s `$biocharLabels` mapping, the
  legacy homepage stat) is untouched, since it's read-only display of
  frozen data, not new entry.
- **Three-way delete-behavior contrast, now spanning three phases** —
  worth stating explicitly as a deliberate spectrum, not drift:
  - `change_logs` (Phase 1): `loggable_id` **unconstrained**, no FK at
    all. An audit record must outlive the row it describes.
  - `tree_planting_measurements` (Phase 2): `tree_planting_id`
    **cascades**. A measurement has no meaning independent of the
    planting it measures — delete the planting, the measurement should
    go with it.
  - `biochar_batches` (Phase 3): both FKs **`nullOnDelete`**. A batch
    represents real material that was produced and applied — deleting
    the `TreePlanting` it happened to be linked to shouldn't erase the
    fact that the material existed and was used; the batch just becomes
    unlinked from that specific planting event, falling back to whatever
    location link (if any) it still has.

  Each choice follows from what the row *represents*: a fact about
  something else (unconstrained), a measurement of a specific thing
  (cascade), or a real-world event that merely references other records
  for context (nullOnDelete).
- **No `ChangeLogger` verify/recorder-split for biochar batches** — the
  investigation's own read was that a biochar batch is asserted once,
  like a `TreePlanting`, not repeatedly attested to by a second party
  like a `TreePlantingMeasurement`. `ChangeLogger` is wired into
  `BiocharBatchController::update()` the same way as
  `PlantingLocationController::update()` from Phase 1 — diff the tracked
  fields (`quantity_kg`, `source`, `batch_reference`, `application_date`,
  `notes`), log only what changed, wrap the update and the log write in
  one `DB::transaction()`. Phase 2's separate verified-by-someone-else
  mechanism was deliberately not built here.
- **`TreeType.source_reference` is required whenever either new numeric
  field is populated**, enforced via `required_with` in
  `TreeTypeController`, not a DB constraint — the columns themselves stay
  nullable so existing species with neither value yet remain valid rows.
  A number without a citation isn't useful for credibility work, so the
  form can't produce that state going forward, even though the schema
  technically allows it for now (import-photo compatibility with older
  species that were entered before this phase).
- **New fields inherit the existing all-public default** on
  `/p/{public_code}` — `wood_density_kg_m3`, `carbon_fraction`, and
  `source_reference` render in the "About the trees" section, with the
  citation shown directly alongside the numbers it supports rather than
  hidden or omitted, per the investigation's finding that nothing
  currently gates TreeType fields from public view.
- **The new homepage stat is additive, not a replacement.** The legacy
  `SUM(number_of_trees * biochar)` stat is untouched in its own tile;
  the new `BiocharBatch::sum('quantity_kg')` stat sits next to it with
  a distinct label ("legacy estimate" vs. "tracked batches"), so a
  reader doesn't assume the two numbers should be added — one is an
  inferred rate-based estimate from historical data, the other is an
  actual recorded quantity from a structurally different table, and
  combining them would misrepresent both.

**Reasoning**

Both changes were classified additive in the original audit, and this
entry keeps them that way: nothing about the existing `tree_plantings`
schema or historical biochar data changes, and `TreeType`'s existing
three fields are untouched. The delete-behavior spectrum across three
phases now gives future work (and future readers of this log) a concrete
set of precedents to reason from — new tables should ask "does this
represent a fact about another row, a measurement of it, or a real thing
that references it," and pick accordingly, rather than defaulting to
whatever the last table did.

---

## 2026-08-19 — Phase 2: tree_planting_measurements, cohort-linked and repeatable

**Context**

Phase 2 of the MRV roadmap targets the gap the original audit flagged as
needing genuine re-architecture, not just new columns: `Inspection` links
only to `PlantingLocation`, never to a specific `TreePlanting` cohort, and
has no structured measurement fields — only a free-text `comment` and a
`verified` boolean. At a location with multiple cohorts (different species/
dates/counts), there was no way to say which planting an inspection
concerned, and no repeatable, typed time-series data (survival, height,
DBH, canopy cover) existed anywhere in the codebase. The Phase 2
investigation confirmed the `InspectionFactory` shows no sign of an
abandoned attempt at a cohort link — this is new structure, not an
extension of something half-built.

**Decision**

Added a new `tree_planting_measurements` table + `TreePlantingMeasurement`
model, always linked to a specific `TreePlanting` via `tree_planting_id`,
with typed columns (`trees_surviving`, `height_avg_cm`, `dbh_avg_cm`,
`canopy_cover_pct`) instead of a free-text-only record. `Inspection` is
**left entirely untouched** — it continues to serve its existing purpose
(location-level narrative site visits) and is not deprecated, migrated, or
replaced by this phase. The two now coexist: `Inspection` for "we visited
this site and here's a note," `TreePlantingMeasurement` for "here's what we
measured on this specific cohort."

- **`tree_planting_id` cascades on delete — the deliberate opposite of
  Phase 1's `change_logs.loggable_id`.** `change_logs` is unconstrained on
  purpose so audit history survives a hard delete of its parent.
  `tree_planting_measurements` cascades on purpose, for the opposite
  reason: a measurement has no meaning independent of the planting it
  measures — there's no value in a "trees surviving" count for a cohort
  that no longer exists in the system. This is a considered contrast
  between the two tables, not an inconsistency: `change_logs` protects a
  fact ("this happened"), `tree_planting_measurements` protects a
  first-class domain record that is meaningless without its parent.
- **Recorder/verifier split**, enforced in the controller, not the model:
  any of the four roles that can create Inspections
  (Admin|SuperAdmin|Monitor|Grower) can record a measurement, but only
  Admin|SuperAdmin|Monitor can verify one (`tree-planting-measurements.verify`
  route, gated by route middleware — 403 for anyone else, consistent with
  how `planting-locations.move` already gates by role at the route level
  rather than in-controller). On top of the role gate, **a user can never
  verify a measurement they recorded themselves**, regardless of role —
  this closes the specific gap where an Admin or Monitor could both record
  and self-attest to the same measurement, which would make "verified"
  meaningless as a credibility signal. Violating this returns a redirect
  with a validation error (`back()->withErrors(...)`), matching the
  existing pattern used by `PlantingLocationController::executeMove` for
  business-rule violations that aren't role/permission failures.
  `verified_by_user_id`/`verified_at` are set via direct attribute
  assignment + `save()` in the controller, not mass-assigned through
  `update()`, so they can never be supplied by a caller through the
  create/edit forms.
- **Edits write to `change_logs`, reusing Phase 1's `ChangeLogger`
  service** — no second audit mechanism was built. Both `update()` (action
  `measurement_updated`, only the fields that actually changed) and
  `verify()` (action `measurement_verified`) follow the same pattern
  established in Phase 1: mutation and its `ChangeLog` write happen inside
  one `DB::transaction()`, per the Phase 1 review fix. Creating a new
  measurement does not write a `ChangeLog` entry — there's no prior state
  to diff against, so the `tree_planting_measurements` row itself is the
  complete record of what was initially recorded.
- **Validation**: `measurement_date` must fall between the parent
  `TreePlanting.planting_date` and today (a measurement can't predate the
  planting it measures, or be dated in the future); `trees_surviving` is
  capped at the parent's `number_of_trees` via a controller-level
  validation rule, not a DB constraint, since `number_of_trees` could
  itself change later and a hard DB check would go stale.
- `tree_planting_id` has an explicit index (not left to chance) — the
  original audit specifically called out `inspections.inspection_date`
  shipping without one and going unnoticed; this phase doesn't repeat
  that.
- No change to `PublicPlantingLocationController` or the public show page
  in this phase — measurement data is not public-facing yet. That's a
  natural fit for the later public API/export phase, once both this
  phase's data and the site-boundary work exist to expose together.

**Reasoning**

This is the one place the original audit explicitly called re-architecture
rather than addition, and this decision treats it that way: a new table
and model, not new columns bolted onto `Inspection`. Reusing `ChangeLogger`
for edit history (rather than building a second audit mechanism) keeps
Phase 1's investment paying off instead of forking the codebase's approach
to auditability in two directions after only one phase.

---

## 2026-08-19 — Phase 1: change_logs audit trail for move/coordinate/status edits

**Context**

Phase 1 of the MRV roadmap (see the 2026-08-16 entry below) targets the
data-loss pattern flagged by the original audit: `PlantingLocationController`
coordinate edits, `PlantingLocationController::executeMove`, and
`TreePlantingController` status changes all perform hard updates with no
history. `status_updated_by` on `tree_plantings` only ever stores the
*current* updater, so every earlier status transition — including who made
it and when — is silently overwritten. A follow-up investigation (also
2026-08-16) confirmed there was no existing history/audit-log package or
table pattern anywhere in this codebase to build on.

**Decision**

Added a single generic `change_logs` table and `ChangeLog` model, written to
explicitly (not via observers) from four mutation points:
`PlantingLocationController::update` (coordinates + status_id),
`PlantingLocationController::executeMove` (one row per moved planting), and
`TreePlantingController::store`/`update` (status set / status changed).

- **`loggable_id` is an indexed `unsignedBigInteger`, not a foreign key.**
  Both `planting_locations` and `tree_plantings` cascade-delete today. A
  constrained FK on `loggable_id` would either block the parent's delete or
  cascade-delete the very history this table exists to preserve — neither
  is acceptable for an audit trail. The tradeoff is that the database can no
  longer guarantee referential integrity here; correctness relies on the
  application always passing a real id, which is acceptable since it's only
  ever written by `ChangeLogger::record()`.
- **Explicit service calls instead of model observers.** The move feature
  uses a query-builder mass update (`TreePlanting::whereIn(...)->update(...)`),
  which does not fire Eloquent model events. An observer watching `saved`/
  `updated` would silently miss every move. Calling `ChangeLogger::record()`
  directly at each mutation point is more verbose but guarantees nothing is
  missed, and keeps the audit-writing visible in a diff instead of hidden in
  an event listener.
- **`changed_by_name` snapshots the user's name at write time**, alongside
  the nullable `changed_by` FK (`nullOnDelete()`, matching the existing
  `status_updated_by` pattern). This directly fixes the attribution-loss
  problem the original `status_updated_by` design has: if the user account
  is later deleted, `changed_by` nulls out, but `changed_by_name` still says
  who made the change.
- The table is append-only (`created_at` only, no `updated_at`); the
  `ChangeLog` model throws on `update()`/`delete()` at the application
  level to keep it that way.
- Added latitude/longitude range validation (`between:-90,90` /
  `between:-180,180`) to `PlantingLocationController::update` while already
  touching that method — previously only `nullable|numeric`, so out-of-range
  coordinates were accepted.
- `status_updated_by` on `tree_plantings` was left in place rather than
  removed, so existing code/views reading it (e.g. `planting-locations/move.blade.php`)
  keep working; `change_logs` is the source of truth for history going
  forward.

Explicitly **not** done in this pass: the `reason` column on `change_logs`
exists (nullable) so a future UI can populate it, but no form currently
exposes it — the move confirmation is still a client-side `confirm()`
dialog. `TreePlanting` status history and `PlantingLocation` status history
now both have entries in `change_logs`, but there is still no UI to *view*
that history; this phase only stops the data loss and writes the records.

**Known pre-existing issue, not touched in this pass**

`MapMarkerService::getMarkers()` (`app/Services/MapMarkerService.php:17-18`)
filters `PlantingLocation` by a `status` key, but the table only has
`status_id` — this filter is dead/silently broken whenever a `status`
filter value is passed. Left as-is per this phase's scope; flagged here for
a future pass.

**Reasoning**

Phases 1–2 were called out in the original roadmap as the most urgent,
since every day on the old schema was unrecoverable data loss. This entry
closes Phase 1's audit-trail gap specifically; Phase 2 (turning `Inspection`
into a structured, cohort-linked repeatable measurement) is unstarted.

---

## 2026-08-16 — MRV/credibility roadmap: sequencing and rationale

**Context**

itacenmu.org currently records tree plantings as aggregate counts per event
(no per-cohort/sample-plot structure), has no time-series growth/survival
data, no audit trail (coordinate edits and the "move plantings" feature
perform hard UPDATEs with no history), no site boundary geometry (points
only), and free-text contributor data. A full MRV/credibility readiness
audit was run via Claude Code (investigation-only, no files modified) to
establish ground truth before any schema changes. See audit findings summary
below.

The goal is **not** to pursue carbon credit certification directly. The goal
is to build credibility, easy verification, and monitoring infrastructure
that could integrate with certification standards (Verra VM0047, Plan Vivo
PM001, or similar) later, at scale (target: hundreds of thousands to
low-millions of trees). This is a separate system from the NRCS volunteer/
membership database — no shared data model or deployment.

**Decision: seven-phase roadmap, in this order**

1. Stop the data-loss pattern + add an audit trail (fix "move plantings"
   and coordinate edits to stop hard-overwriting history; close the gap
   between `status_updated_by` storing only the current value vs. real
   status history)
2. Restructure measurement into repeatable, cohort-linked records (turn
   `Inspection` from a location-level free-text note into a structured,
   repeatable measurement tied to a specific `TreePlanting` cohort)
3. Enrich reference data: species carbon factors (growth rate, wood
   density on `TreeType`) + biochar as its own batched entity (quantity,
   unit, source, batch — currently a fixed-dropdown decimal column)
4. Capture GPS and photo provenance properly (record capture method and
   accuracy already being read and discarded; parse EXIF on photo upload
   into DB fields independent of the file)
5. Decide and implement site boundary geometry (platform decision: stay
   on MySQL with a JSON/WKT polygon column, or move to a spatial-capable
   store such as PostGIS — not yet decided, see "Open questions" below)
6. Replace the free-text contributor blob with a structured
   Contributor/Organization model + pivot to planting events
7. Build the public API / GeoJSON export layer (read-only endpoint(s)
   exposing cohorts, measurements, and boundaries so an independent party
   can pull raw data and verify it directly)

Scale hardening (fixing unbounded eager loads in `MapController` and
`MapMarkerService`, adding indexes on filtered columns like `division_id`,
`status`, `planting_date`) is treated as an **ongoing, parallel** track
rather than a numbered phase, since it's a query-layer concern independent
of the schema work above and becomes urgent well before six-figure row
counts are reached.

**Reasoning**

- Phases 1–2 come first because they stop the loss of information that is
  being generated *right now*. Every day on the current schema is data
  that later phases cannot retroactively recover — this is treated as more
  urgent than any feature work.
- Phase 2 is the one place the audit explicitly flagged as needing
  re-architecture rather than addition: `TreePlanting` already functions
  like a cohort (date + species + count + location), but there is nothing
  underneath it to hold repeated, structured measurements over time.
- Phases 3–7 were classified by the audit as additive — layerable on top
  of the existing model without breaking it — and are ordered by how much
  they unblock later work (reference data before boundary geometry before
  the public API, which depends on both).
- The site-boundary/geometry decision (Phase 5) is called out separately
  because it is a genuine platform decision (storage engine, query
  capability) rather than a schema addition, and deserves its own
  deliberate discussion and entry in this log before implementation.
- Phases 3 onward may be reordered depending on which certification
  pathway (Verra VM0047 vs. Plan Vivo PM001) ends up being the closer
  target, since that choice affects which specific fields the reference
  data and boundary work need to match. This should be revisited once
  Phases 1–2 are complete.

**Open questions (to resolve before Phase 5)**

- MySQL + JSON/WKT polygon column, vs. migrating to a spatial-capable
  database (e.g. PostGIS)? Affects query layer, not just one table.
- Which certification pathway (if any) to design the reference-data and
  boundary schema toward: Verra VM0047 (area-based or census-based),
  Plan Vivo PM001, or neither — building only for independent credibility
  without targeting a specific standard's schema.

**Audit findings summary (for reference)**

Full detail lives in the Claude Code investigation output from 2026-08-16
(not committed as a separate file — see conversation history / this log).
Key gaps identified: no per-tree/cohort structure (aggregate `int` only),
no time-series growth/survival data, biochar as a single dropdown-constrained
decimal column, point-only lat/lng with no boundary geometry, no soft
deletes/audit trail anywhere in the codebase, no species carbon/growth
reference fields, free-text contributor blob, no public API/export endpoint,
and several unbounded eager-load patterns (`MapController::index`,
`MapMarkerService::getMarkers()`) that will not scale past low six figures
of rows without query rework.
