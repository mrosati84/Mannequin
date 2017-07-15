<?php


namespace LastCall\Mannequin\Core\Tests\Subscriber;

use LastCall\Mannequin\Core\Pattern\PatternCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use LastCall\Mannequin\Core\Pattern\PatternInterface;
use LastCall\Mannequin\Core\Event\PatternDiscoveryEvent;
use LastCall\Mannequin\Core\Event\PatternEvents;

trait DiscoverySubscriberTestTrait {

  protected function dispatchDiscover(EventSubscriberInterface $subscriber, PatternInterface $pattern, PatternCollection $collection = NULL): PatternDiscoveryEvent {
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber($subscriber);
    $collection = $collection ?: new PatternCollection();
    return $dispatcher->dispatch(PatternEvents::DISCOVER, new PatternDiscoveryEvent($pattern, $collection));
  }

}