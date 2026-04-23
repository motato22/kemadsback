<?php

namespace App\Console\Commands\Advertising;

use App\Models\Advertising\AdvCampaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ExpireCampaignsCommand extends Command
{
    protected $signature   = 'adv:expire-campaigns';
    protected $description = 'Marca como expiradas las campañas cuya fecha ends_at ha pasado.';

    public function handle(): int
    {
        $expired = AdvCampaign::whereIn('status', ['active', 'scheduled'])
            ->where('ends_at', '<', now())
            ->count();

        if ($expired === 0) {
            $this->info('No hay campañas que expirar.');
            return self::SUCCESS;
        }

        AdvCampaign::whereIn('status', ['active', 'scheduled'])
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);

        // Invalida caché para que las tablets reciban el estado actualizado
        Cache::tags(['adv:sync'])->flush();

        $this->info("Se marcaron {$expired} campaña(s) como expiradas.");

        return self::SUCCESS;
    }
}
