<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Http\Requests\Advertising\ProvisionRequest;
use App\Models\Advertising\AdvTablet;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProvisionController extends Controller
{
    /**
     * POST /api/adv/provision
     *
     * Endpoint público (sin autenticación previa) para el aprovisionamiento inicial.
     * La tablet envía su device_id escaneado desde el QR generado en Filament.
     * Devuelve el token Sanctum que usará en todas las requests futuras.
     *
     * El QR de aprovisionamiento contiene: { endpoint, provision_secret, unit_id }
     * El provision_secret es un valor generado en Filament y tiene expiración de 24h.
     */
    public function store(ProvisionRequest $request): JsonResponse
    {
        $deviceId        = $request->input('device_id');
        $unitId          = $request->input('unit_id');
        $provisionSecret = $request->input('provision_secret');

        // 1. Buscamos la tablet solo por unit_id primero
        $tablet = AdvTablet::where('unit_id', $unitId)->first();

        // 2. Si no existe la unidad
        if (! $tablet) {
            return response()->json(['error' => "La unidad {$unitId} no existe en el sistema."], 404);
        }

        // 3. Si ya está activa
        if ($tablet->status === 'active') {
            return response()->json([
                'error'  => 'Esta unidad ya ha sido activada previamente.',
                'status' => 'already_active',
            ], 422);
        }

        // 4. Si tiene otro estado que no sea provisioning
        if ($tablet->status !== 'provisioning') {
            return response()->json(['error' => 'La unidad no está en modo de aprovisionamiento.'], 403);
        }

        // 5. Validación del secret (caché TTL 24h, generado en Filament)
        $expectedSecret = cache("adv:provision_secret:{$unitId}");
        if (! $expectedSecret || ! hash_equals($expectedSecret, $provisionSecret)) {
            return response()->json(['error' => 'Código de aprovisionamiento inválido o expirado.'], 403);
        }

        $plainToken = null;

        DB::transaction(function () use ($tablet, $deviceId, &$plainToken) {
            $tablet->tokens()->delete();

            $token = $tablet->createToken("tablet-{$tablet->unit_id}");
            $plainToken = $token->plainTextToken;

            $tablet->update([
                'device_id'         => $deviceId,
                'status'            => 'active',
                'sanctum_token_id'  => $token->accessToken->id,
            ]);

            cache()->forget("adv:provision_secret:{$tablet->unit_id}");
        });

        return response()->json([
            'status'      => 'provisioned',
            'token'       => $plainToken ?? '',
            'tablet_id'   => $tablet->id,
            'server_time' => now()->toIso8601String(),
        ], 201);
    }
}
