<?php

namespace CodeZero\ComposerPreloadFiles;

use Composer\Autoload\AutoloadGenerator as ComposerAutoloadGenerator;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\CompletePackage;
use Composer\Pcre\Preg;
use Composer\Util\Filesystem;
use Composer\Util\Platform;

class AutoloadGenerator extends ComposerAutoloadGenerator
{
    /**
     * Add preload files to the autoload files.
     *
     * @param \Composer\Composer $composer
     * @param \Composer\IO\IOInterface $io
     * @param \Composer\Util\Filesystem $filesystem
     *
     * @return void
     */
    public function addPreloadFilesToAutoloadFiles(Composer $composer, IOInterface $io, Filesystem $filesystem)
    {
        $preloadFiles = $this->parsePreloadFiles($composer, $filesystem);

        if (count($preloadFiles) === 0) {
            return;
        }

        $io->writeError('<info>Adding preload files to the autoload files.</info>');

        // Some pathfinding...
        // Do not remove double realpath() calls.
        // Fixes failing Windows realpath() implementation.
        // See https://bugs.php.net/bug.php?id=72738
        $basePath = $filesystem->normalizePath(realpath(realpath(Platform::getCwd())));
        $vendorDir = $composer->getConfig()->get('vendor-dir');
        $vendorPath = $filesystem->normalizePath(realpath(realpath($vendorDir)));
        $targetDir = $vendorPath.'/composer';

        $this->prependPreloadFilesToAutoloadFilesFile($filesystem, $preloadFiles, $targetDir, $basePath, $vendorPath);
        $this->regenerateAutoloadStaticFile($filesystem, $targetDir, $basePath, $vendorPath);
    }

    /**
     * Prepend preload files to the 'autoload_files.php' file.
     *
     * @param \Composer\Util\Filesystem $filesystem
     * @param array $preloadFiles
     * @param string $targetDir
     * @param string $basePath
     * @param string $vendorPath
     *
     * @return void
     */
    protected function prependPreloadFilesToAutoloadFilesFile(Filesystem $filesystem, $preloadFiles, $targetDir, $basePath, $vendorPath)
    {
        $vendorPathCode = $filesystem->findShortestPathCode(realpath($targetDir), $vendorPath, true);
        $appBaseDirCode = $filesystem->findShortestPathCode($vendorPath, $basePath, true);
        $appBaseDirCode = str_replace('__DIR__', '$vendorDir', $appBaseDirCode);
        $autoloadFilesFilePath = $targetDir.'/autoload_files.php';

        // Merge preload files with original files.
        $originalFiles = $this->getOriginalAutoloadFiles($autoloadFilesFilePath);
        $allFiles = array_merge($preloadFiles, $originalFiles);

        // Write new 'autoload_files.php'.
        $filesystem->filePutContentsIfModified(
            $autoloadFilesFilePath,
            $this->getIncludeFilesFile($allFiles, $filesystem, $basePath, $vendorPath, $vendorPathCode, $appBaseDirCode)
        );
    }

    /**
     * Regenerate the 'autoload_static.php' file.
     *
     * @param \Composer\Util\Filesystem $filesystem
     * @param string $targetDir
     * @param string $basePath
     * @param string $vendorPath
     *
     * @return void
     */
    protected function regenerateAutoloadStaticFile(Filesystem $filesystem, $targetDir, $basePath, $vendorPath)
    {
        // Get the class name suffix from 'autoload.php'.
        // https://github.com/composer/composer/blob/main/src/Composer/Autoload/AutoloadGenerator.php#L390-L392
        $autoloadContent = file_get_contents($vendorPath.'/autoload.php');
        $suffix = null;
        if (Preg::isMatch('{ComposerAutoloaderInit([^:\s]+)::}', $autoloadContent, $match)) {
            $suffix = $match[1];
        }

        // Write new 'autoload_static.php'.
        $filesystem->filePutContentsIfModified(
            $targetDir.'/autoload_static.php',
            $this->getStaticFile($suffix, $targetDir, $vendorPath, $basePath)
        );
    }

    /**
     * Get the original files to autoload.
     *
     * @param string $autoloadFilesFilePath
     *
     * @return array
     */
    protected function getOriginalAutoloadFiles($autoloadFilesFilePath)
    {
        if (file_exists($autoloadFilesFilePath)) {
            return include $autoloadFilesFilePath;
        }

        return [];
    }

    /**
     * Parse preload files from the root package and all vendor packages.
     *
     * @param \Composer\Composer $composer
     * @param \Composer\Util\Filesystem $filesystem
     *
     * @return array
     */
    protected function parsePreloadFiles(Composer $composer, Filesystem $filesystem)
    {
        $installationManager = $composer->getInstallationManager();
        $preloadFilesKey = 'preload-files';
        $preloadFiles = [];

        // Do not remove double realpath() calls.
        // Fixes failing Windows realpath() implementation.
        // See https://bugs.php.net/bug.php?id=72738
        $basePath = $filesystem->normalizePath(realpath(realpath(Platform::getCwd())));

        $rootPackage = $composer->getPackage();
        $rootPackageConfig = $rootPackage->getExtra();
        $rootPackagePreloadFiles = $rootPackageConfig[$preloadFilesKey] ?? [];

        foreach ($rootPackagePreloadFiles as $file) {
            $identifier = $this->getFileIdentifier($rootPackage, $file);
            $preloadFiles[$identifier] = $filesystem->normalizePath($basePath . '/' . $file);
        }

        $otherPackages = $composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();

        foreach ($otherPackages as $package) {
            if ( ! ($package instanceof CompletePackage) || strtolower($package->getType()) === 'metapackage') {
                continue;
            }

            $packageBaseDir = $filesystem->normalizePath($installationManager->getInstallPath($package));
            $packageConfig = $package->getExtra();
            $packagePreloadFiles = $packageConfig[$preloadFilesKey] ?? [];

            foreach ($packagePreloadFiles as $file) {
                $identifier = $this->getFileIdentifier($package, $file);
                $preloadFiles[$identifier] = $filesystem->normalizePath($packageBaseDir . '/' . $file);
            }
        }

        return $preloadFiles;
    }
}
