<?php

namespace CodeZero\ComposerPreloadFiles;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    /**
     * Composer instance.
     *
     * @var \Composer\Composer
     */
    protected $composer;

    /**
     * The Input/Output helper interface.
     *
     * @var \Composer\IO\IOInterface
     */
    protected $io;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            //
        ];
    }

    /**
     * Apply plugin modifications to Composer.
     *
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     *
     * @return void
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    /**
     * Remove any hooks from Composer.
     *
     * This will be called when a plugin is deactivated before being
     * uninstalled, but also before it gets upgraded to a new version
     * so the old one can be deactivated and the new one activated.
     *
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     *
     * @return void
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
        //
    }

    /**
     * Prepare the plugin to be uninstalled.
     *
     * This will be called after deactivate.
     *
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     *
     * @return void
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
        //
    }
}
