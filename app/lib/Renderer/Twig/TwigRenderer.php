<?php

namespace Honeybee\FrameworkBinding\Silex\Renderer\Twig;

use Honeybee\FrameworkBinding\Silex\Renderer\TemplateRendererInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig_Environment;
use Twig_Loader_String;

class TwigRenderer implements TemplateRendererInterface
{
    protected $twig;

    protected $filesystem;

    /**
     * @param Twig_Environment $twig configured twig instance
     * @param Filesystem $filesystem filesystem instance with dumpFile() method
     */
    public function __construct(Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    /**
     * Renders the given template using the given variables and returns the rendered result.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process; IGNORED for twig
     *
     * @return mixed result of the rendered template
     */
    public function render($template, array $data = [], array $settings = [])
    {
        return $this->twig->render($template, $data);
    }

    /**
     * Renders the wanted template using the given variables as context into the specified target location.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param string $target_file local filesystem target path location (including file name)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process; IGNORED for twig
     *
     * @throws Symfony\Component\Filesystem\Exception\IOException if target file cannot be written
     */
    public function renderToFile($template, $target_file, array $data = [], array $settings = [])
    {
        $content = $this->twig->render($template, $data, $settings);
        $this->filesystem->dumpFile($target_file, $content);
    }

    /**
     * Renders the given template source code string using the given data and returns the rendered string.
     *
     * @param string $template template source code or identifier (like a (relative) file path)
     * @param array $data variables and context data for template rendering
     * @param array $settings optional settings to configure rendering process; IGNORED for twig
     *
     * @return string result of the rendered template source
     */
    public function renderToString($template, array $data = [], array $settings = [])
    {
        $original_loader = $this->twig->getLoader();
        $this->twig->setLoader(new Twig_Loader_String());
        $content = $this->twig->render($template, $data, $settings);
        $this->twig->setLoader($original_loader);

        return $content;
    }
}
