<?php

namespace App\Http\Controllers\Api\Advertising;

use App\Http\Controllers\Controller;
use App\Models\Advertising\AdvCampaign;
use Illuminate\Http\RedirectResponse;

class CampaignQrController extends Controller
{
    /**
     * GET /api/adv/campaigns/{campaign}/qr
     * Incrementa el contador de escaneos y redirige a la URL de destino.
     */
    public function redirect(AdvCampaign $campaign): RedirectResponse
    {
        // Si no tiene QR activado o no hay URL, mandamos un 404
        if (! $campaign->has_qr || empty($campaign->qr_url)) {
            abort(404, 'Código QR no disponible para esta campaña.');
        }

        // Incrementamos el contador
        $campaign->increment('qr_scans');

        // Redirigimos a la URL final configurada en Filament
        return redirect()->away($campaign->qr_url);
    }
}
