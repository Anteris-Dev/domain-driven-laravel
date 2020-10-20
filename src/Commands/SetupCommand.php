<?php

namespace Anteris\DomainDrivenLaravel\Commands;

use Anteris\DomainDrivenLaravel\Models\LaravelDomainModel;
use Illuminate\Console\Command;

/**
 * Sets up a default Laravel application as a Domain Driven Application
 */
class SetupCommand extends Command
{
    protected $signature    = 'domain:setup {domain : Name of the domain you would like to work under}';
    protected $description  = 'Turns this Laravel application into a DDD app.';

    public function handle()
    {
        $this->newLine(2);
        
        // Confirm the developer wants to organize their application before modifying files
        if (! $this->confirm('This will modify core Laravel files. Are you sure you would like to continue?')) {
            return 0;
        }

        $this->newLine(1);
        $this->comment('Beginning organization of app directory.');

        // Organize the application.
        $laravelDomain = new LaravelDomainModel(app()->basePath());
        $laravelDomain->setDomain($this->argument('domain'));
        $laravelDomain->save();

        // Write a success message
        $this->newLine(2);
        $this->info('Successfully organized your Laravel application!');
        $this->newLine(2);
    }
}
