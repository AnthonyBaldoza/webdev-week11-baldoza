## Scopes (Event model)

| Scope | What it does |
|---|---|
| `upcoming()` | Events that start from now onward |
| `soonest()` | Orders events by `starts_at` ascending |
| `atVenue($venueId)` | Events at one venue (takes a parameter) |
| `withDressCode($code)` | Filters by `details->dress_code` (arrow form) |

Chained in `/events/filter`: `Event::withDressCode($code)->upcoming()->soonest()`.

## Query counts (/events, first 50 events)

| Version | Queries |
|---|---|
| Naive (lazy loading) | 101 |
| Fixed (`with('venue')->withCount('participants')`) | 2 |

The naive version sends one query for the events, then one for each event's venue and one for each event's participants, so the number grows with the number of rows (the N+1 problem). The fixed version loads all venues in one extra query and counts participants inside the main query, so the number stays the same no matter how many events are listed.

## Query plans (20,000 events)

| Query | Index | Plan used | Execution time |
|---|---|---|---|
| Arrow `details->>'dress_code' = 'costume'` | None | Seq Scan | ? ms |
| Containment `details @> {"dress_code":"costume"}` | None | Seq Scan | ? ms |
| Full-text `'telescope'` | None | Seq Scan | ? ms |
| Arrow `'costume'` | btree on `(details->>'dress_code')` | Bitmap Index Scan | ? ms |
| Containment `'costume'` | GIN `jsonb_path_ops` on `details` | Bitmap Index Scan | ? ms |
| Full-text `'telescope'` | GIN on `to_tsvector('english', description)` | Bitmap Index Scan | ? ms |
| `description LIKE '%telescope%'` | GIN full-text index (exists) | Seq Scan | ? ms |

The last row has an index that is not used: the full-text index stores `to_tsvector('english', description)` and only works with the `@@` operator, so a `LIKE '%telescope%'` query cannot use it and PostgreSQL scans the whole table.

## Why indexes are in migrations (Step 8)

`migrate:fresh` drops every table and rebuilds the database only from the migration files, so an index created by hand in pgAdmin would disappear. The schema builder cannot create expression indexes or GIN indexes with `jsonb_path_ops`, so those use `DB::statement()` inside a migration.

## Choosing columns (Step 9)

- JSONB: `details.dress_code` (and `tags`). It is optional extra information that can differ per event type, so it can change without a new migration.
- Normal column: `venue_id` and `starts_at`. They are used for joins, foreign key rules, sorting, and filtering on every request, so they need a real type, constraints, and a simple index.