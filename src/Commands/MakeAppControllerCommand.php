<?php

namespace Anteris\DomainDrivenLaravel\Commands;

class MakeAppControllerCommand extends AbstractMakeAppCommand
{
    protected $signature    = 'make:app:controller {layer} {name}';
    protected $description  = 'Create a new controller within the application layer';
    protected $type         = 'Controller';

    public function getClassNamespace(): string
    {
        return 'Controllers';
    }

    public function getClassStub(): string
    {
        return 'ControllerStub.php';
    }
}
