<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Models\Advertising\AdvDriverShift;
use App\Models\Advertising\AdvTablet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverShiftController extends Controller
{
    /**
     * GET /api/adv/driver-shift/active
     *
     * Devuelve el turno activo actual de la tablet, si existe.
     */
    public function active(Request $request): JsonResponse
    {
        /** @var AdvTablet $tablet */
        $tablet = $request->user();

        $shift = $tablet->activeShift;

        return response()->json([
            'shift' => $shift ? [
                'id'          => $shift->id,
                'driver_name' => $shift->driver_name,
                'driver_code' => $shift->driver_code,
                'started_at'  => $shift->started_at->toIso8601String(),
            ] : null,
        ]);
    }

    /**
     * POST /api/adv/driver-shift
     *
     * Inicia un nuevo turno para la tablet. Cierra el turno anterior si existe.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var AdvTablet $tablet */
        $tablet = $request->user();

        $validated = $request->validate([
            'driver_name' => ['required', 'string', 'max:80'],
            'driver_code' => ['nullable', 'string', 'max:20'],
        ]);

        // Cierra turno activo anterior
        $tablet->driverShifts()->whereNull('ended_at')->update(['ended_at' => now()]);

        $shift = $tablet->driverShifts()->create([
            'driver_name' => $validated['driver_name'],
            'driver_code' => $validated['driver_code'] ?? null,
            'started_at'  => now(),
        ]);

        return response()->json([
            'status' => 'ok',
            'shift'  => [
                'id'          => $shift->id,
                'driver_name' => $shift->driver_name,
                'started_at'  => $shift->started_at->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * PUT /api/adv/driver-shift/close
     *
     * Cierra el turno activo de la tablet.
     */
    public function close(Request $request): JsonResponse
    {
        /** @var AdvTablet $tablet */
        $tablet = $request->user();

        $tablet->driverShifts()->whereNull('ended_at')->update(['ended_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}
