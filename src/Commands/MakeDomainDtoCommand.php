<?php

namespace Anteris\DomainDrivenLaravel\Commands;

class MakeDomainDtoCommand extends AbstractMakeDomainCommand
{
    protected $signature    = 'make:domain:dto {subdomain} {name}';
    protected $description  = 'Create a new data transfer object within the domain';
    protected $type         = 'Data Transfer Object';

    public function getClassNamespace(): string
    {
        return 'DataTransferObjects';
    }

    public function getClassStub(): string
    {
        return 'DtoStub.php';
    }
}
