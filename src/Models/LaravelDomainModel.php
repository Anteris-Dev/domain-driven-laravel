<?php

namespace Anteris\DomainDrivenLaravel\Models;

use Anteris\DomainDrivenLaravel\Exceptions\FilesystemException;
use Anteris\DomainDrivenLaravel\Exceptions\InvalidDomainException;
use Anteris\DomainDrivenLaravel\Support\Composer\Composer;
use Anteris\DomainDrivenLaravel\Support\Filesystem\Filesystem;
use Anteris\DomainDrivenLaravel\Support\Helpers\Path;
use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Transforms a default Laravel application into a Domain Driven experience.
 * (Includes support for Fortify and Jetstream)
 */
class LaravelDomainModel
{
    /** @var string This represents the base application directory. */
    protected string $baseDir;

    /** @var Composer An API for composer so we can manage dumpautoloads, etc. */
    protected Composer $composer;

    /** @var string The domain we want to set. */
    protected ?string $domain;

    /** @var Filesystem An API for interacting with the filesystem. */
    protected Filesystem $filesystem;

    /**
     * Sets up the class and its dependencies.
     *
     * @param  string  $baseDir  The directory that Laravel is installed in.
     */
    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;

        // Setup our dependencies
        $this->filesystem   = new Filesystem;
        $this->composer     = new Composer($this->filesystem, $this->baseDir);

        // Preload our values
        $this->domain = $this->composer->getConfig('laravel.domain');
    }

    /**
     * Sets the name of the domain.
     */
    public function setDomain(string $domain): void
    {
        if (in_array($domain, ['App', 'Support'])) {
            throw new InvalidDomainException("$domain is a reserved namespace!");
        }

        $this->domain = $domain;
    }

    /**
     * Gets the name of the domain.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Persists the changes we have made to the model.
     */
    public function save(): void
    {
        if (! isset($this->domain) || $this->domain == null) {
            throw new InvalidDomainException('Please set a domain before attempting to save your changes!');
        }

        if ($this->composer->getConfig('laravel.domain') == null) {
            $this->create();

            return;
        }

        $this->update();
    }

    /**
     * Creates a DDD application from our Laravel application.
     * @return void
     */
    protected function create(): void
    {
        // Handle Jetstream and Fortify before other stuff
        if ($this->isFortifyInstalled()) {
            $this->moveFortifyFiles();
        }

        if ($this->isJetstreamInstalled()) {
            $this->moveJetstreamFiles();
        }

        $this->moveModels();
        $this->moveLaravelSupport();

        // Create an application layer directory
        $this->filesystem->makeDirectoryOrFail(Path::app($this->baseDir));

        // Update the Laravel directory in bootstrap
        $bootstrapFile  = Path::laravelBootstrapFile($this->baseDir);
        $bootstrap      = $this->filesystem->get($bootstrapFile);

        if (strpos($bootstrap, 'useAppPath(') !== false) {
            return;
        }

        $updates = "\$app->useAppPath(__DIR__ . '/../app/Support');" . PHP_EOL . PHP_EOL . 'return $app;';

        $this->filesystem->replaceInFile('return $app;', $updates, $bootstrapFile);

        // Delete the actions directory if empty
        $actionsDir = Path::laravelActions($this->baseDir);

        if ($this->filesystem->exists($actionsDir)) {
            $files = $this->filesystem->files($actionsDir);
            if (count($files) <= 0) {
                $this->filesystem->deleteDirectory($actionsDir);
            }
        }

        // Now update composer
        $this->composer->updateAutoload('App\\', 'app/App');
        $this->composer->updateAutoload("{$this->domain}\\", "app/{$this->domain}");
        $this->composer->updateAutoload('Support\\', 'app/Support');
        $this->composer->setConfig('laravel.domain', $this->domain);
        $this->composer->dumpAutoloads();
    }

    /**
     * Updates the domain name from an old domain to a new one.
     * @return void
     */
    protected function update(): void
    {
        $oldDomain          = $this->composer->getConfig('laravel.domain');
        $newDomain          = $this->domain;
        $oldDomainDirectory = Path::domain($this->baseDir, $oldDomain);
        $newDomainDirectory = Path::domain($this->baseDir, $newDomain);

        if (strtolower($oldDomain) == strtolower($newDomain)) {
            return;
        }

        if ($this->filesystem->missing($oldDomainDirectory)) {
            throw new FilesystemException("Unable to find the old domain directory: $oldDomainDirectory");
        }

        $this->filesystem->moveDirectory($oldDomainDirectory, $newDomainDirectory, true);
        $this->updateNamespace("{$oldDomain}\\", "{$newDomain}\\");
        $this->composer->removeAutoload("{$oldDomain}\\");
        $this->composer->updateAutoload("{$newDomain}\\", "app/{$newDomain}");
        $this->composer->setConfig('laravel.domain', $newDomain);
        $this->composer->dumpAutoloads();
    }

    /***************************************************************************
     * Movers that assist in changing the location of files.
     **************************************************************************/

    /**
     * Moves all files relating to Fortify. If Jetstream teams is installed,
     * these are moved to a "Team" subdomain. If not, they are moved to a "User"
     * subdomain.
     *
     * @return void
     */
    protected function moveFortifyFiles(): void
    {
        $actionsDir = Path::fortifyActions($this->baseDir);
        $actions    = $this->filesystem->files($actionsDir);
        $subdomain  = 'User';

        if ($this->jetstreamHasTeams()) {
            $subdomain = 'Team';
        }

        // Move each action to the subdomain
        foreach ($actions as $action) {
            $this->moveToSubdomain($subdomain, 'Actions/Fortify', $action);
        }

        $this->filesystem->deleteDirectory($actionsDir);
    }

    /**
     * Moves all files relating to Jetstream. If teams is installed, these are
     * moved to a "Team" subdomain. If not, they are moved to a "User" subdomain.
     *
     * @return void
     */
    protected function moveJetstreamFiles(): void
    {
        $hasTeams   = $this->jetstreamHasTeams();
        $subdomain  = 'User';
        $actionsDir = Path::jetstreamActions($this->baseDir);
        $actions    = $this->filesystem->files($actionsDir);
        $models     = [ 'User.php' ];

        // If teams is installed, we will adjust where we place these files and
        // add those models
        if ($hasTeams) {
            $subdomain  = 'Team';
            $models     = array_merge($models, [
                'Membership.php',
                'Team.php',
            ]);
        }

        // Now move the Jetstream actions
        foreach ($actions as $action) {
            $this->moveToSubdomain($subdomain, 'Actions/Jetstream', $action);
        }

        $this->filesystem->deleteDirectory($actionsDir, false);

        // Next move the Jetstream models
        $models = $this->filesystem->findInDir(
            Path::laravelModels($this->baseDir),
            $models
        );

        foreach ($models as $model) {
            $this->moveToSubdomain($subdomain, "Models", $model);
        }

        // Now we just have to update the Jetstream service provider
        $namespace = "\\{$this->domain}\\{$subdomain}\\Models";
        $updates   = "        Jetstream::useUserModel({$namespace}\User::class);";

        if ($hasTeams) {
            $updates .= PHP_EOL;
            $updates .= "        Jetstream::useTeamModel({$namespace}\Team::class);" . PHP_EOL;
            $updates .= "        Jetstream::useMembershipModel({$namespace}\Membership::class);";
        }

        $this->filesystem->writeLineAfterText(
            'Jetstream::deleteUsersUsing(DeleteUser::class);',
            $updates,
            Path::jetstreamServiceProvider($this->baseDir)
        );
    }

    /**
     * Moves any Laravel files (e.g. Service providers, Abstract controllers, etc.)
     * that are necessary but do not really have a place in the domain to a
     * Support directory.
     *
     * @return void
     */
    protected function moveLaravelSupport(): void
    {
        $supportDirectory = Path::support($this->baseDir);
        $this->filesystem->makeDirectoryOrFail($supportDirectory);

        // Move all default directories except "Actions" and "Models" into a support directory
        $appDirectories = $this->filesystem->directories(
            Path::laravelApp($this->baseDir)
        );

        foreach ($appDirectories as $appDirectory) {
            $appDirectoryName = basename($appDirectory);

            if (
                $appDirectoryName == 'Actions' ||
                $appDirectoryName == 'Models' ||
                $appDirectoryName == 'Support' ||
                $appDirectoryName == $this->domain
            ) {
                continue;
            }

            $this->filesystem->move($appDirectory, "{$supportDirectory}/{$appDirectoryName}");
        }

        // Update the namespace
        $this->updateNamespace('App\\', 'Support\\');
    }

    /**
     * Moves any models that exist to the domain. The subdomain these are placed
     * in is decided by the singularized and studly form of the model name.
     *
     * @return void
     */
    protected function moveModels(): void
    {
        $modelsDir = Path::laravelModels($this->baseDir);

        if (! $this->filesystem->exists($modelsDir)) {
            return;
        }

        $domainDirectory = Path::domain($this->baseDir, $this->domain);
        $this->filesystem->makeDirectoryOrFail($domainDirectory);

        $models = $this->filesystem->allFiles($modelsDir, false);

        foreach ($models as $model) {
            $subdomain = Str::studly(Str::singular($model->getFilenameWithoutExtension()));
            $this->moveToSubdomain($subdomain, 'Models', $model);
        }

        $this->filesystem->deleteDirectory($modelsDir);
    }

    /**
     * Moves a file from its current location to a subdomain folder and updates
     * the namespace.
     *
     * @param  string  $subdomain
     * @param  string  $subdomainSubDirectory
     * @param  SplFileInfo  $file
     *
     * @return void
     */
    protected function moveToSubdomain(
        string $subdomain,
        string $subdomainSubDirectory,
        SplFileInfo $file
    ): void {
        $domainDirectory    = "{$this->baseDir}/app/{$this->domain}";
        $subdomainDirectory = "{$domainDirectory}/{$subdomain}/{$subdomainSubDirectory}";

        $this->filesystem->makeDirectoryOrFail($subdomainDirectory);

        // This convoluted section turns directory path into a namespace
        // based on PSR-4
        $oldNamespace = ltrim(str_replace($this->baseDir, '', realpath($file->getPath())), '/');
        $oldNamespace = ucfirst(str_replace('/', '\\', $oldNamespace));
        $oldNamespace .= '\\' . $file->getFilenameWithoutExtension();

        // Move the file
        $this->filesystem->move(
            $file->getRealPath(),
            "$subdomainDirectory/" . $file->getFilename()
        );

        // Update the namespace in the class file We are doing this now
        // because otherwise updating the App\\ namespace will mess with it.
        $cleanDirectory = str_replace('/', '\\', $subdomainSubDirectory);
        $modelNamespace = basename($domainDirectory) . "\\{$subdomain}\\{$cleanDirectory}";

        $this->filesystem->replaceInFile(
            "namespace App\\$cleanDirectory",
            "namespace $modelNamespace",
            "$subdomainDirectory/" . $file->getFilename()
        );

        // Now update the namespace in all files
        $this->updateNamespace(
            $oldNamespace,
            "$modelNamespace\\" . $file->getFilenameWithoutExtension()
        );
    }

    /***************************************************************************
     * Helpers that are used in other parts of the class.
     **************************************************************************/

    /**
     * Determines whether or not Fortify is currently installed.
     * @return bool
     */
    protected function isFortifyInstalled(): bool
    {
        $fortifyActions = Path::fortifyActions($this->baseDir);

        if (! $this->filesystem->exists($fortifyActions)) {
            return false;
        }

        return true;
    }

    /**
     * Determines whether or not Jetstream is installed.
     * @return bool
     */
    protected function isJetstreamInstalled(): bool
    {
        $jetstreamActions = Path::jetstreamActions($this->baseDir);

        if (! $this->filesystem->exists($jetstreamActions)) {
            return false;
        }

        return true;
    }

    /**
     * Determines whether or not Jetstream has teams installed.
     * @return bool
     */
    protected function jetstreamHasTeams(): bool
    {
        $jetstreamModels = [
            "{$this->baseDir}/app/Models/Membership.php",
            "{$this->baseDir}/app/Models/Team.php",
        ];

        foreach ($jetstreamModels as $model) {
            if (! $this->filesystem->exists($model)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Updates the namespace in the app, bootstrap, and config directories.
     *
     * @param  string  $oldNamespace
     * @param  string  $newNamespace
     *
     * @return void
     */
    protected function updateNamespace(string $oldNamespace, string $newNamespace): void
    {
        $files = array_merge(
            $this->filesystem->allFiles(Path::laravelApp($this->baseDir)),
            $this->filesystem->allFiles(Path::laravelBootstrap($this->baseDir)),
            $this->filesystem->allFiles(Path::laravelConfig($this->baseDir)),
            $this->filesystem->allFiles(Path::laravelDatabase($this->baseDir)),
        );

        foreach ($files as $file) {
            $this->filesystem->replaceInFile($oldNamespace, $newNamespace, $file);
        }
    }
}
