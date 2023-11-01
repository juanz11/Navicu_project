<?php namespace System\Console;

use Composer\Installer\PackageEvent;
use Composer\Script\Event;

/**
 * ComposerScript is a collection of composer script logic
 *
 * @package october\system
 * @author Alexey Bobkov, Samuel Georges
 */
class ComposerScript
{
    /**
     * postAutoloadDump
     */
    public static function postAutoloadDump(Event $event)
    {
        static::passthruArtisan('package:discover');
    }

    /**
     * postUpdateCmd occurs after the update command has been executed, or after
     * the install command has been executed without a lock file present.
     */
    public static function postUpdateCmd(Event $event)
    {
        static::passthruArtisan('october:util set build');

        static::passthruArtisan('october:mirror --composer');
    }

    /**
     * prePackageUninstall occurs before a package is uninstalled
     */
    public static function prePackageUninstall(PackageEvent $event)
    {
        $package = $event->getOperation()->getPackage();

        if (self::isOfType($package, 'plugin')) {
            static::passthruArtisan("plugin:remove ${package} --composer");
        }

        // Purge discovered package cache to prevent errors
        if (file_exists($packagesMeta = __DIR__ . '/../../../storage/framework/packages.php')) {
            @unlink($packagesMeta);
        }
    }

    /**
     * isOfType checks if a package is a plugin or theme
     *
     * rainlab-vanilla-theme dev-master, theme -> true
     */
    protected static function isOfType(string $package, string $type): bool
    {
        if (substr($package, -strlen('-'.$type)) === (string) '-'.$type) {
            return true;
        }

        if (strpos($package, '-'.$type.'-') !== false) {
            return true;
        }

        return false;
    }

    /**
     * passthruArtisan
     */
    protected static function passthruArtisan($command, &$errCode = null)
    {
        passthru('"'.PHP_BINARY.'" artisan ' .$command);
    }
}
