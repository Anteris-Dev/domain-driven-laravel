<?php

namespace Anteris\DomainDrivenLaravel\Commands;

use Illuminate\Console\GeneratorCommand;

/**
 * This class does the heavy lifting in resolving application layer paths and
 * namespaces.
 */
abstract class AbstractMakeAppCommand extends GeneratorCommand
{
    /**
     * The nested namespace this class should have in the application layer (e.g. "Controllers").
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
     * Returns the file path of the stub for this command.
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
        $layer  = trim($this->argument('layer'));

        return "App\\{$layer}\\" . $this->getClassNamespace();
    }
}
