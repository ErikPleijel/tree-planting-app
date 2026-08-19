# Decisions Log — itacenmu.org

This file records architectural and strategic decisions for the tree-planting
app, including the reasoning behind them, so future changes (by Erik, by
Claude Code, or by anyone else) can understand *why* something was built a
certain way, not just *what* was built.

Format: newest entries at the top. Each entry has a date, a short title,
the context that prompted it, the decision, and the reasoning.

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
