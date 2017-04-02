<?php

namespace Honeylex\Console\Scaffold;

use Honeylex\Config\ConfigProviderInterface;
use Honeylex\Renderer\Twig\Extension\ProjectExtension;
use Honeylex\Renderer\Twig\TwigRenderer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Finds the given skeleton and copies/renders all of the skeleton's files to
 * the target location using the specified data variables for rendering of
 * files as well as file and directory names.
 */
class SkeletonGenerator implements SkeletonGeneratorInterface
{
    const DIRECTORY_MODE = 0755;

    const TEMPLATE_FILENAME_EXTENSION = '.tmpl.twig';

    protected $data;

    protected $overwriteEnabled = true;

    protected $report = [];

    protected $reportingEnabled = true;

    protected $configProvider;

    protected $skeletonName;

    protected $lookupPaths;

    protected $targetPath;

    protected $twigStringRenderer = null;

    /**
     * Creates a new generator instance.
     *
     * @param string $skeletonName name of the skeleton in one of the skeleton lookup paths
     * @param array $lookupPaths list of paths to search when looking for a directory-skeleton
     * @param string $targetPath full path to the target location
     * @param array $data variables to use as context for rendering via twig
     */
    public function __construct(
        ConfigProviderInterface $configProvider,
        $skeletonName,
        array $lookupPaths,
        $targetPath,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->skeletonName = $skeletonName;
        $this->lookupPaths = $lookupPaths;
        $this->targetPath = $targetPath;
        $this->fs = new Filesystem;
        $this->data = $data;

        $this->twigStringRenderer = TwigRenderer::create(
            [
                'twig_extensions' => [ new ProjectExtension($this->configProvider) ],
                'twig_options' => [
                    'autoescape' => false,
                    'cache' => false,
                    'debug' => true,
                    'strict_variables' => true
                ]
            ]
        );
    }

    /**
     * Uses the current skeleton to create a file/folder structure in the
     * target location.
     * 1. create target folders returned from "getFolderStructure"
     * 2. copy all files from source to target (while creating necessary folders)
     * 3. render all ".tmpl.twig" files in the target folder to the same named files without that extension
     *
     * Override this method if you want to alter the default generator behaviour.
     */
    public function generate()
    {
        $this->createFolders();
        $this->copyFiles();
        $this->renderTemplates();
    }

    /**
     * Enables or disables the overwriting of files in their target location.
     */
    public function enableOverwriting($overwrite = true)
    {
        return $this->overwriteEnabled = (bool)$overwrite;
    }

    /**
     * Enables or disables report generation. A report is an array with string
     * messages of what was done by the generator.
     */
    public function enableReporting($report = true)
    {
        return $this->reportingEnabled = (bool)$report;
    }

    /**
     * @return array of string messages
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * Creates all folders specified by getFolderStructure within the target
     * location.
     */
    protected function createFolders()
    {
        foreach ($this->getFolderStructure() as $folder) {
            $newFolder = $this->twigStringRenderer->renderToString($folder, $this->data);
            $this->fs->mkdir($this->targetPath.DIRECTORY_SEPARATOR.$newFolder, self::DIRECTORY_MODE);

            $msg = '[mkdir] '.$this->targetPath.DIRECTORY_SEPARATOR.$newFolder;
            if ($this->reportingEnabled) {
                $this->report[] =$msg;
            }
        }
    }

    /**
     * Copies all files from the source location to the target location.
     */
    protected function copyFiles()
    {
        $skeletonFinder = new SkeletonFinder($this->lookupPaths);
        $sourcePath = $skeletonFinder->findByName($this->skeletonName)->getRealpath();

        $finder = $this->getFinderForFilesToCopy($sourcePath);

        foreach ($finder as $file) {
            $targetFilePath = $this->targetPath.DIRECTORY_SEPARATOR.$file->getRelativePathname();
            $targetFilePath = $this->twigStringRenderer->renderToString($targetFilePath, $this->data);

            $this->fs->copy($file->getRealpath(), $targetFilePath, $this->overwriteEnabled);

            $msg = '[copy] '.$file->getRealpath().' => '.$targetFilePath;

            if ($this->reportingEnabled) {
                $this->report[] = $msg;
            }
        }
    }

    /**
     * @param string $sourcePath path to copy files from
     *
     * @return Finder instance configured with all files to copy from the source path
     */
    protected function getFinderForFilesToCopy($sourcePath)
    {
        $finder = new Finder;
        $finder->files()->in($sourcePath);

        return $finder;
    }

    /**
     * Renders all files within the target location whose extension is
     * ".tmpl.twig" onto a file that has the same name without that extension.
     * After the rendering all the ".tmpl.twig" files will be deleted in the
     * target location.
     */
    protected function renderTemplates()
    {
        $finder = new Finder;

        $finder->files()->name('*'.self::TEMPLATE_FILENAME_EXTENSION)->in($this->targetPath);

        $twigRenderer = TwigRenderer::create(
            [
                'twig_extensions' => [ new ProjectExtension($this->configProvider) ],
                'twig_options' => [
                    'autoescape' => false,
                    'cache' => false,
                    'debug' => true,
                    'strict_variables' => true
                ],
                'template_paths' => [
                    $this->targetPath
                ]
            ]
        );

        foreach ($finder as $template) {
            $targetFilePath = $template->getPath().DIRECTORY_SEPARATOR.
                $template->getBasename(self::TEMPLATE_FILENAME_EXTENSION);

            if (!file_exists($targetFilePath) || (is_readable($targetFilePath) && $this->overwriteEnabled)) {
                $twigRenderer->renderToFile(
                    $template->getRelativePathname(),
                    $targetFilePath,
                    $this->data
                );
            }

            $msg = '[render] '.$template->getRelativePathname().' => '.$targetFilePath;

            if ($this->reportingEnabled) {
                $this->report[] = $msg;
            }
        }

        $this->fs->remove($finder);
    }

    /**
     * Returns an array of folders to create in the target path.
     * The directories can be deeply nested and contain twig code that gets
     * rendered via the simple twig renderer. That means you can return an
     * array with this as an example string:
     * 'some/deeply/nested/{{crate_name}}/{{namespace|replace({"\\\\":"/"})}}/assets'
     * if "crate_name" and "namespace" are known string parameters.
     *
     * @return array of folders to create (relative paths)
     */
    protected function getFolderStructure()
    {
        return [];
    }
}
