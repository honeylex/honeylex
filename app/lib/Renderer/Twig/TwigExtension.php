<?php

namespace Honeybee\FrameworkBinding\Silex\Renderer\Twig;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\FrameworkBinding\Silex\Config\ConfigProviderInterface;
use Honeybee\Infrastructure\Config\SettingsInterface;
use Honeybee\Infrastructure\Workflow\WorkflowServiceInterface;
use Honeybee\Projection\ProjectionInterface;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;
use Workflux\Error\Error;

class TwigExtension extends Twig_Extension
{
    protected $configProvider;

    protected $workflowService;

    public function __construct(ConfigProviderInterface $configProvider, WorkflowServiceInterface $workflowService)
    {
        $this->configProvider = $configProvider;
        $this->workflowService = $workflowService;
    }

    public function getName()
    {
        return 'project';
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('config', [ $this, 'getConfig' ])
        ];
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('snake', function ($string) {
                return StringToolkit::asSnakeCase($string);
            }),
            new Twig_SimpleFilter('camel', function ($string) {
                return StringToolkit::asCamelCase($string);
            }),
            new Twig_SimpleFilter('accepts_event', function (ProjectionInterface $entity, $event) {
                $stateMachine = $this->workflowService->getStateMachine($entity->getType());
                try {
                    $acceptedEvents = $this->workflowService->getSupportedEventsFor(
                        $stateMachine,
                        $entity->getWorkflowState()
                    );
                } catch (Error $error) {
                    return false;
                }
                return in_array($event, $acceptedEvents);
            })
        ];
    }

    public function getConfig($path, $default = null)
    {
        return $this->configProvider->getSetting($path, $default);
    }
}
