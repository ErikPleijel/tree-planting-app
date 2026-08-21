# Decisions Log — itacenmu.org

This file records architectural and strategic decisions for the tree-planting
app, including the reasoning behind them, so future changes (by Erik, by
Claude Code, or by anyone else) can understand *why* something was built a
certain way, not just *what* was built.

Format: newest entries at the top. Each entry has a date, a short title,
the context that prompted it, the decision, and the reasoning.

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
