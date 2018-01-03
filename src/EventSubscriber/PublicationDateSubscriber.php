<?php

namespace Drupal\publication_date\EventSubscriber;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\workbench_moderation\Event\WorkbenchModerationEvents;
use Drupal\workbench_moderation\Event\WorkbenchModerationTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Publication Date event subscriber.
 */
class PublicationDateSubscriber implements EventSubscriberInterface {

  /**
   * Handle workbench moderation state transition.
   */
  public function onWorkbenchModerationStateTransition(WorkbenchModerationTransitionEvent $event) {
    if ($event->getEntity()->getEntityTypeId() == 'node') {
      $event->getEntity()->get('published_at')->preSave();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      WorkbenchModerationEvents::STATE_TRANSITION => ['onWorkbenchModerationStateTransition'],
    ];
  }

}
