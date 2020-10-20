<?php

namespace Anteris\DomainDrivenLaravel\Providers;

use Anteris\DomainDrivenLaravel\Commands\MakeAppControllerCommand;
use Anteris\DomainDrivenLaravel\Commands\MakeAppViewModelCommand;
use Anteris\DomainDrivenLaravel\Commands\MakeDomainActionCommand;
use Anteris\DomainDrivenLaravel\Commands\MakeDomainDtoCommand;
use Anteris\DomainDrivenLaravel\Commands\MakeDomainModelCommand;
use Anteris\DomainDrivenLaravel\Commands\SetupCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the commands provided by this package with Laravel.
 */
class DomainDrivenLaravelProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeAppControllerCommand::class,
                MakeAppViewModelCommand::class,
                MakeDomainActionCommand::class,
                MakeDomainDtoCommand::class,
                MakeDomainModelCommand::class,
                SetupCommand::class,
            ]);
        }
    }
}
