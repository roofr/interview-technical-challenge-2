<?php

namespace App\Services;

use App\DTOs\ParkingLotStatusDTO;
use App\DTOs\ParkRequestDTO;
use App\DTOs\SpotTypeStatusDTO;
use App\Enums\SpotType;
use App\Enums\VehicleType;
use App\Exceptions\ParkingException;
use App\Models\ParkingLot;
use App\Models\ParkingSession;
use App\Repositories\ParkingSessionRepository;
use App\Repositories\ParkingSpotRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ParkingService
{
    public function __construct(
        private readonly ParkingSpotRepository $spotRepository,
        private readonly ParkingSessionRepository $sessionRepository,
    ) {}

    public function park(int $spotId, ParkRequestDTO $dto): ParkingSession
    {
        return DB::transaction(function () use ($spotId, $dto) {
            $spot = $this->spotRepository->lockForUpdate($spotId);

            if ($spot->is_occupied) {
                throw ParkingException::spotNotAvailable($spot->id);
            }

            $acceptedTypes = $dto->vehicleType->acceptedSpotTypes();

            if ($dto->vehicleType === VehicleType::Van && $spot->type === SpotType::Regular) {
                return $this->parkVanOnConsecutiveSpots($spot->id, $dto);
            }

            if (! in_array($spot->type, $acceptedTypes, true)) {
                throw ParkingException::incompatibleVehicle(
                    $dto->vehicleType->value,
                    $spot->type->value
                );
            }

            $spots = new Collection([$spot]);
            $this->spotRepository->markOccupied($spots);

            return $this->sessionRepository->create($dto->vehicleType, $spots, $dto->licensePlate);
        });
    }

    public function unpark(int $spotId): void
    {
        DB::transaction(function () use ($spotId) {
            $spot = $this->spotRepository->lockForUpdate($spotId);

            if (! $spot->is_occupied) {
                throw ParkingException::spotNotOccupied($spot->id);
            }

            $session = $this->sessionRepository->findBySpotId($spotId);

            if (! $session) {
                throw ParkingException::spotNotOccupied($spot->id);
            }

            $this->spotRepository->markAvailable($session->spots);
            $this->sessionRepository->delete($session);
        });
    }

    public function getLotStatus(int $lotId): ParkingLotStatusDTO
    {
        ParkingLot::findOrFail($lotId);

        $aggregates = $this->spotRepository->aggregateByType($lotId);

        $totalSpots = array_sum(array_column($aggregates, 'total'));
        $availableSpots = array_sum(array_column($aggregates, 'available'));

        $byType = array_map(
            fn ($row) => new SpotTypeStatusDTO($row['total'], $row['available']),
            $aggregates
        );

        return new ParkingLotStatusDTO($totalSpots, $availableSpots, $byType);
    }

    private function parkVanOnConsecutiveSpots(int $anchorSpotId, ParkRequestDTO $dto): ParkingSession
    {
        $needed = $dto->vehicleType->consecutiveSpotsNeeded();
        $spots = $this->spotRepository->findConsecutiveRegularSpots($anchorSpotId, $needed);

        if ($spots->count() !== $needed) {
            throw ParkingException::noConsecutiveSpotsAvailable($needed);
        }

        $this->spotRepository->markOccupied($spots);

        return $this->sessionRepository->create($dto->vehicleType, $spots, $dto->licensePlate);
    }
}
