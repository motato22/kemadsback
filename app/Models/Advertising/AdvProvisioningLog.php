<?php

namespace App\Models\Advertising;

use Illuminate\Database\Eloquent\Model;

class AdvProvisioningLog extends Model
{
    protected $table = 'adv_provisioning_logs';

    protected $fillable = [
        'ip_address',
        'user_agent',
        'status',
        'notes',
    ];
}
