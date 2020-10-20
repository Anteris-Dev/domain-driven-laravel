<?php

namespace Anteris\DomainDrivenLaravel\Support\Composer;

use Illuminate\Support\Composer as IlluminateComposer;

/**
 * Extends the Composer class provided by Illuminate to give us the ability to
 * write to the composer.json file.
 */
class Composer extends IlluminateComposer
{
    /**
     * Returns the composer file in array form.
     * @return array
     */
    public function getComposerFile(): array
    {
        return json_decode(
            file_get_contents("{$this->workingPath}/composer.json"),
            true
        );
    }

    /**
     * Sets the contents of the composer file.
     *
     * @param  array  $contents
     * @return void
     */
    public function setComposerFile(array $contents): void
    {
        file_put_contents(
            "{$this->workingPath}/composer.json",
            json_encode($contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Retrieves a specific configuration value from the composer file.
     *
     * @return  mixed
     */
    public function getConfig(string $key)
    {
        $pointer  = $this->getComposerFile();
        $keyArray = explode('.', "extra.{$key}");

        foreach ($keyArray as $subKey) {
            if (! isset($pointer[$subKey])) {
                return null;
            }

            $pointer = $pointer[$subKey];
        }

        return $pointer;
    }

    /**
     * Sets a specific configuration value in the composer file.
     *
     * @param  string  $key  The key to set.
     * @param  mixed   $value  The value the key should have.
     */
    public function setConfig(string $key, $value): void
    {
        $command = array_merge($this->findComposer(), ["config", "extra.{$key}", $value]);
        $this->getProcess($command)->run();
    }

    /**
     * Removes an autoload from the composer file.
     */
    public function removeAutoload(string $namespace): void
    {
        $composerArray = $this->getComposerFile();
        unset($composerArray['autoload']['psr-4'][$namespace]);

        $this->setComposerFile($composerArray);
    }

    /**
     * Sets an Autoload key in the configuration.
     *
     * @param  string  $namespace
     * @param  string  $directory
     *
     * @return void
     */
    public function updateAutoload(string $namespace, string $directory): void
    {
        $composerArray                                  = $this->getComposerFile();
        $composerArray['autoload']['psr-4'][$namespace] = $directory;

        $this->setComposerFile($composerArray);
    }
}
