<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Cautela;
use App\Models\Ferramenta;
use App\Models\Militar;
use App\Models\Aeronave;
use App\Policies\CautelaPolicy;
use App\Policies\FerramentaPolicy;
use App\Policies\MilitarPolicy;
use App\Policies\AeronavePolicy;

/**
 * ServiceProvider: AuthServiceProvider
 * 
 * Registra as policies de autorização do sistema.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Cautela::class => CautelaPolicy::class,
        Ferramenta::class => FerramentaPolicy::class,
        Militar::class => MilitarPolicy::class,
        Aeronave::class => AeronavePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}


