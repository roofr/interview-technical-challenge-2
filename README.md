# Technical Interview Challenge 2

## Installation

1. Install docker compose https://docs.docker.com/compose/install/#scenario-one-install-docker-desktop
2. Clone the repository
2. Run `docker-compose up`
4. Run `docker-compose exec my-app composer install -o`
3. Run `docker-compose exec my-app php artisan migrate`
4. Load in browser http://localhost:8081

## Endpoints

| Method | Endpoint                        | Description                  |
|--------|---------------------------------|------------------------------|
| GET    | `/api/parking-lot`              | Lot status (totals by type)  |
| POST   | `/api/parking-spot/{id}/park`   | Park a vehicle               |
| POST   | `/api/parking-spot/{id}/unpark` | Unpark a vehicle             |

### Park request body

```json
{
    "vehicle_type": "car",
    "license_plate": "ABC-1234"
}
```

Valid `vehicle_type` values: `motorcycle`, `car`, `van`.

## Parking rules

- **Motorcycle** — fits in any spot type (motorcycle, regular, van)
- **Car** — fits in regular or van spots only
- **Van** — fits in a dedicated van spot (1 spot), or takes 3 consecutive regular spots in the same section

## Running tests

```bash
docker-compose exec my-app php artisan test
```

Tests run against a separate `myapp_test` database and do not affect seeded data.

## Architecture decisions

- **Repository pattern** — database queries are isolated in `ParkingSpotRepository` and `ParkingSessionRepository`, keeping the service layer free of Eloquent details.
- **Service layer** — `ParkingService` owns all business logic and coordinates repositories inside `DB::transaction()`.
- **DTOs** — `ParkRequestDTO` decouples the HTTP layer from the service layer; the controller converts the validated request into a DTO before passing it down.
- **PHP Enums as the single source of truth** — `SpotType` and `VehicleType` backed enums are used directly in migrations via `array_column(SpotType::cases(), 'value')`, eliminating any drift between PHP and the database schema.
- **Atomic van parking** — multi-spot reservation uses `lockForUpdate()` inside a transaction so two concurrent van requests cannot claim the same spots.
- **Pivot table for spots ↔ sessions** — `parking_session_spot` supports the van case (1 session → 3 spots) while keeping single-spot vehicles consistent with the same model.

## Known limitations and tradeoffs

### Single-lot assumption
`GET /api/parking-lot` always returns `ParkingLot::firstOrFail()`. The data model supports multiple lots (every section has a `parking_lot_id` FK), but the API has no route parameter for it. Extending to multi-lot would require changing the route to `/api/parking-lot/{id}` and updating the controller.

### Spot selection is caller-driven
The client must know and supply a specific `spot_id` to park. There is no "find me the next available spot" endpoint — the client is expected to read the lot status and choose a spot from the response. A real-world system would likely offer an auto-assign flow.

### Van consecutive-spot logic is anchor-based
When parking a van in regular spots, the API takes the requested spot as the anchor and looks for the next 2 spots by `number` within the same section (`whereBetween(number, [anchor, anchor + 2])`). If the middle spot is occupied the request fails — it does not search for another group of 3. The client must pick a valid starting spot.

### No authentication
All endpoints are public. A production system would need auth (API keys or OAuth) and per-user or per-operator scoping.

### No pagination on lot status
The `GET /api/parking-lot` response embeds all sections and all spots. For large lots this could be a heavy payload. Pagination or a separate `/sections` endpoint would be needed at scale.

### Sessions are deleted on unpark
`ParkingSessionRepository::delete()` hard-deletes the session record. There is no history of past parking events. Soft deletes or an audit log would be needed for reporting.
