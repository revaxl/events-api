<?php

namespace App\Models;

use App\Exceptions\NoAvailableTicketException;
use App\Exceptions\BiggerThanTicketLimitException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class Event extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'date', 'availability'
    ];

    protected $casts = [
        'availability' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * @param array $data
     * @return bool
     * @throws Throwable
     */
    public function makeReservation(array $data): bool
    {
        // Check if the user is trying to make another reservation on the same event
        if (!is_null($this->getEventReservation($data['user_id']))) {
            // If yes, then update their previous event reservation
            return $this->updateReservation($data);
        }
        // Check if the event still have available tickets to reserve
        $this->isStillAvailable();
        // Or if the reservation number is less than the amount of tickets available
        $this->isReservationBiggerThanAvailability($data['reservations']);
        // create new Reservation for the event
        $this->createReservation($data);
        // subtract the amount of reservations the user wants from the original availability
        $this->availability -= $data['reservations'];
        $this->save();
        return true;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Throwable
     */
    public function updateReservation(array $data): bool
    {
        // Get the reservation data for the event
        $reservation = $this->userNotHaveReservation($data['user_id']);
        // Add the amount of reservations back to the original event availability
        $this->availability += $reservation->reservations;
        // Check if the  new amount is still OK or not
        $this->isReservationBiggerThanAvailability($data['reservations']);
        // Subtract the new amount from the event availability and update the reservation model as well
        $reservation->reservations = $data['reservations'];
        $reservation->save();
        $this->availability -= $data['reservations'];
        $this->save();
        return true;
    }

    /**
     * @param int $userId
     * @return bool
     * @throws Throwable
     */
    public function cancelReservation(int $userId): bool
    {
        // Get the reservation data related to this event
        $reservation = $this->userNotHaveReservation($userId);
        // Return the reserved tickets to the event ticket limit
        $this->availability += $reservation->reservations;
        // Save the event new modification
        $this->save();
        // Delete the reservation from db
        $reservation->delete();
        return true;
    }

    /**
     * @throws NoAvailableTicketException
     */
    private function isStillAvailable()
    {
        if ($this->availability === 0) {
            throw new NoAvailableTicketException();
        }
    }

    /**
     * @param int $reservations
     * @throws BiggerThanTicketLimitException
     */
    private function isReservationBiggerThanAvailability(int $reservations)
    {
        if ($this->availability - $reservations < 0) {
            throw new BiggerThanTicketLimitException();
        }
    }

    /**
     * @param array $data
     * @return Reservation
     */
    private function createReservation(array $data): Reservation
    {
        return Reservation::create([
            'user_id' => $data['user_id'],
            'event_id' => $this->id,
            'reservations' => $data['reservations'],
        ]);
    }


    /**
     * @param int $userId
     * @return Model|null
     */
    private function getEventReservation(int $userId): ?Model
    {
        return $this->reservations()->where('user_id', $userId)->first();
    }

    /**
     * @throws Throwable
     */
    private function userNotHaveReservation(int $userId): Model
    {
        throw_unless($reservation = $this->getEventReservation($userId), ModelNotFoundException::class, );
        return $reservation;
    }
}
