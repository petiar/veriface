<?php

namespace Drupal\veriface;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class EventSubscriber implements EventSubscriberInterface {

  public static function getSubscribedEvents() {
    $events[KernelEvents::TERMINATE][] = ['VerifaceLoad', 20];
    return $events;
  }

  public function VerifaceLoad($event) {
    if (!\Drupal::config('veriface.settings')->get('api_key')) {
      \Drupal::messenger()->addWarning(t('VeriFace API key is not set, please, do that <a href=":url">here</a>.',
        [
          ':url' => Url::fromRoute('veriface.settings_form')->toString(),
        ]));
    }
  }
}
