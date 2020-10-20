<?php

namespace Anteris\DomainDrivenLaravel\Commands;

use Anteris\DomainDrivenLaravel\Models\LaravelDomainModel;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;

/**
 * This class does the heavy lifting in resolving domain layer paths and
 * namespaces.
 */
abstract class AbstractMakeDomainCommand extends GeneratorCommand
{
    /** @var LaravelDomainModel An API for interacting with the domain. */
    private LaravelDomainModel $domain;

    /**
     * Sets up the command to be able to interact with the domain.
     *
     * @param  Filesystem  $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct($filesystem);

        $this->domain = new LaravelDomainModel(app()->basePath());
    }

    /**
     * The nested namespace this class should have in the subdomain (e.g. "Models").
     */
    abstract public function getClassNamespace(): string;

    /**
     * The filename of the stub this class has.
     */
    abstract public function getClassStub(): string;

    /**
     * Returns the path this file should be created at.
     * @return string
     */
    protected function getPath($name)
    {
        $file = str_replace('\\', '/', $name) . '.php';

        return app()->basePath() . "/app/{$file}";
    }

    /**
     * Returns the stub for this command.
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/../../stubs/' . $this->getClassStub();
    }

    /**
     * Returns the namespace for the generated file.
     * @return string
     */
    protected function rootNamespace()
    {
        $domain    = $this->domain->getDomain();
        $subdomain = trim($this->argument('subdomain'));

        return "{$domain}\\{$subdomain}\\" . $this->getClassNamespace();
    }
}
