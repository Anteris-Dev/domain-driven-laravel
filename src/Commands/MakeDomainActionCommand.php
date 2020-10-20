<?php

namespace Anteris\DomainDrivenLaravel\Commands;

class MakeDomainActionCommand extends AbstractMakeDomainCommand
{
    protected $signature    = 'make:domain:action {subdomain} {name}';
    protected $description  = 'Create a new action class within the domain';
    protected $type         = 'Action';

    public function getClassNamespace(): string
    {
        return 'Actions';
    }

    public function getClassStub(): string
    {
        return 'ActionStub.php';
    }
}
