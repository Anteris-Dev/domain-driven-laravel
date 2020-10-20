<?php

namespace Anteris\DomainDrivenLaravel\Commands;

class MakeDomainModelCommand extends AbstractMakeDomainCommand
{
    protected $signature    = 'make:domain:model {subdomain} {name}';
    protected $description  = 'Create a new model within the domain';
    protected $type         = 'Model';

    public function getClassNamespace(): string
    {
        return 'Models';
    }

    public function getClassStub(): string
    {
        return 'ModelStub.php';
    }
}
