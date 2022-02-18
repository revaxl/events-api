<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EventController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $events = Event::all(['id', 'name', 'description', 'date', 'availability']);
        return response()->json($events);
    }

    /**
     * @param Request $request
     * @param int $eventId
     * @return JsonResponse
     * @throws Throwable
     */
    public function reserve(Request $request, int $eventId): JsonResponse
    {
        try {
            DB::beginTransaction();
            $event = $this->getEventOrFail($eventId);
            $validated = $this->validate($request, [
                'user_id' => 'required|numeric|exists:users,id',
                'reservations' => 'required|numeric'
            ]);

            if (!$event->makeReservation($validated)) {
                return response()->json(['message' => __('response.fail')], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::commit();
            return response()->json(['message' => __('response.success')]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Request $request
     * @param int $eventId
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request, int $eventId): JsonResponse
    {
        try {
            DB::beginTransaction();
            $event = $this->getEventOrFail($eventId);
            $validated = $this->validate($request, [
                'user_id' => 'required|numeric|exists:users,id',
                'reservations' => 'required|numeric'
            ]);

            if (!$event->updateReservation($validated)) {
                return response()->json(['message' => __('response.fail')], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::commit();
            return response()->json(['message' => __('response.success')]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param Request $request
     * @param int $eventId
     * @return JsonResponse
     * @throws Throwable
     */
    public function cancel(Request $request, int $eventId): JsonResponse
    {
        try {
            DB::beginTransaction();
            $event = $this->getEventOrFail($eventId);
            $validated = $this->validate($request, [
                'user_id' => 'required|exists:users,id',
            ]);

            if (!$event->cancelReservation($validated['user_id'])) {
                return response()->json(['message' => __('response.fail')], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            DB::commit();
            return response()->json(['message' => __('response.success')]);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage());
            Log::error($exception->getTraceAsString());
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * @param int $eventId
     * @return Event
     */
    private function getEventOrFail(int $eventId): Event
    {
        return Event::findOrFail($eventId);
    }
}
