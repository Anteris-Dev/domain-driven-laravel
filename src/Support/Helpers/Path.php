<?php

namespace Anteris\DomainDrivenLaravel\Support\Helpers;

/**
 * This class stores the paths for Laravel directories, making it an easy one
 * stop place to update anything that changes in the future.
 */
class Path
{
    public static function app($baseDir): string
    {
        return "{$baseDir}/app/App";
    }

    public static function composerFile($baseDir): string
    {
        return "{$baseDir}/composer.json";
    }

    public static function domain($baseDir, $domain): string
    {
        return "{$baseDir}/app/{$domain}";
    }

    public static function fortifyActions($baseDir): string
    {
        return "{$baseDir}/app/Actions/Fortify";
    }

    public static function jetstreamActions($baseDir): string
    {
        return "{$baseDir}/app/Actions/Jetstream";
    }

    public static function jetstreamServiceProvider($baseDir): string
    {
        return "{$baseDir}/app/Providers/JetstreamServiceProvider.php";
    }

    public static function laravelActions($baseDir): string
    {
        return "{$baseDir}/app/Actions";
    }

    public static function laravelApp($baseDir): string
    {
        return "{$baseDir}/app";
    }

    public static function laravelBootstrap($baseDir): string
    {
        return "{$baseDir}/bootstrap";
    }

    public static function laravelBootstrapFile($baseDir): string
    {
        return "{$baseDir}/bootstrap/app.php";
    }

    public static function laravelConfig($baseDir): string
    {
        return "{$baseDir}/config";
    }

    public static function laravelDatabase($baseDir): string
    {
        return "{$baseDir}/database";
    }

    public static function laravelModels($baseDir): string
    {
        return "{$baseDir}/app/Models";
    }

    public static function support($baseDir): string
    {
        return "{$baseDir}/app/Support";
    }
}
