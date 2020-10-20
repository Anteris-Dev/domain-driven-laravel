<?php

namespace Anteris\DomainDrivenLaravel\Support\Filesystem;

use Anteris\DomainDrivenLaravel\Exceptions\FilesystemException;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Extends the Filesystem class provided by Illuminate to give use more methods.
 */
class Filesystem extends IlluminateFilesystem
{
    /**
     * Looks for specific files in the directory.
     *
     * @param  string  $directory  The directory to be searched.
     * @param  array  $files  The files to look for.
     */
    public function findInDir(string $directory, array $files)
    {
        return Finder::create()->files()
                ->in($directory)
                ->depth(0)
                ->filter(function (SplFileInfo $fileInfo) use ($files) {
                    return in_array($fileInfo->getFilename(), $files);
                })
                ->sortByName();
    }

    /**
     * Attempts to create a directory and throws an exception if unable to do so.
     *
     * @param  string  $directory
     */
    public function makeDirectoryOrFail(string $directory): void
    {
        if ($this->exists($directory)) {
            return;
        }

        if (! $this->makeDirectory($directory, 0755, true)) {
            throw new FilesystemException("Unable to create directory {$directory}!");
        }
    }

    /**
     * Finds a string in the file and replaces it with another string.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $file
     */
    public function replaceInFile(string $search, string $replace, string $file)
    {
        return file_put_contents(
            $file,
            str_replace(
                $search,
                $replace,
                file_get_contents($file)
            )
        );
    }

    /**
     * Writes a new line of content after the searched for text.
     *
     * @param  string  $search  The line to search.
     * @param  string  $addition  The content to write after that line.
     * @param  string  $file  The file to write to.
     */
    public function writeLineAfterText(string $search, string $addition, string $file): void
    {
        $newFile = '';

        foreach ($this->lines($file) as $line) {
            $newFile .= $line . PHP_EOL;

            if (strpos($line, $search) !== false) {
                $newFile .= $addition . PHP_EOL;
            }
        }

        $this->replace($file, $newFile);
    }
}
