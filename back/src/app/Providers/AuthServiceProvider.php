<?php

namespace App\Providers;

use App\Models\Atividade;
use App\Models\Problema;
use App\Models\Professor;
use App\Models\User;
use App\Policies\AtividadePolicy;
use App\Policies\ProblemaPolicy;
use App\Policies\ProfessorPolicy;
use App\Policies\AlunoPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Atividade::class => AtividadePolicy::class,
        Problema::class => ProblemaPolicy::class,
        Professor::class => ProfessorPolicy::class,
        User::class => AlunoPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
