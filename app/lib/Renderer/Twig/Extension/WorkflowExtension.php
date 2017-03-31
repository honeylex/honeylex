<?php

namespace Honeybee\FrameworkBinding\Silex\Renderer\Twig\Extension;

use Honeybee\Infrastructure\Workflow\WorkflowServiceInterface;
use Honeybee\Projection\ProjectionInterface;
use Twig_Extension;
use Twig_SimpleFilter;
use Workflux\Error\Error;

class WorkflowExtension extends Twig_Extension
{
    protected $workflowService;

    public function __construct(WorkflowServiceInterface $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function getName()
    {
        return 'workflow';
    }

    public function getFilters()
    {
        return [
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
}
