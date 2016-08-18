<?php

namespace Honeybee\FrameworkBinding\Silex\Twig;

use Honeybee\Common\Util\StringToolkit;
use Honeybee\Projection\ProjectionInterface;
use Honeybee\Projection\WorkflowSubject;
use Twig_Extension;
use Twig_SimpleFilter;
use Workflux\Error\Error;

class TwigExtension extends Twig_Extension
{
    public function getName()
    {
        return 'project';
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
                $stateMachine = $entity->getType()->getWorkflowStateMachine();
                try {
                    $acceptedEvents = WorkflowSubject::getSupportedEventsFor($stateMachine, $entity->getWorkflowState());
                } catch (Error $error) {
                    return false;
                }
                return in_array($event, $acceptedEvents);
            })
        ];
    }
}
