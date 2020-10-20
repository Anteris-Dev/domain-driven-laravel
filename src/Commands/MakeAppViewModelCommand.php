<?php

namespace Anteris\DomainDrivenLaravel\Commands;

class MakeAppViewModelCommand extends AbstractMakeAppCommand
{
    protected $signature    = 'make:app:viewmodel {layer} {name}';
    protected $description  = 'Create a new view model within the application layer';
    protected $type         = 'View Model';

    public function getClassNamespace(): string
    {
        return 'ViewModels';
    }

    public function getClassStub(): string
    {
        return 'ViewModelStub.php';
    }
}
