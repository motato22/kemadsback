<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignSuperAdmin extends Command
{
    protected $signature = 'adv:super-admin {email} {--password=password}';

    protected $description = 'Crea (o actualiza) un usuario y le asigna el rol super_admin en el panel de publicidad';

    public function handle(): int
    {
        $email    = $this->argument('email');
        $password = $this->option('password');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => 'Admin KEM',
                'password' => $password,
            ]
        );

        $user->assignRole('super_admin');

        $this->info("✅ Usuario [{$email}] tiene ahora el rol super_admin.");
        $this->line("   Contraseña: {$password}");

        return self::SUCCESS;
    }
}
