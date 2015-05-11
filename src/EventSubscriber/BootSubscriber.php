<?php /**
 * @file
 * Contains \Drupal\bugsnag\EventSubscriber\BootSubscriber.
 */

namespace Drupal\bugsnag\EventSubscriber;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BootSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [KernelEvents::REQUEST => ['onEvent', 0]];
  }

  public function onEvent(\Symfony\Component\HttpKernel\Event\GetResponseEvent $event) {
    $apikey = trim(\Drupal::config('bugsnag.settings')->get('bugsnag_apikey'));
    if (file_exists(_bugsnag_get_library_path()) && !empty($apikey)) {
      $user = \Drupal::currentUser();
      global $bugsnag_client;

      require_once _bugsnag_get_library_path();
      $bugsnag_client = new Bugsnag_Client(\Drupal::config('bugsnag.settings')->get('bugsnag_apikey'));

      if ($user->uid) {
        $bugsnag_client->setUser([
          'id' => $user->uid,
          'name' => $user->name,
          'email' => $user->mail,
        ]);
      }

      set_error_handler([$bugsnag_client, 'errorHandler']);
      set_exception_handler([$bugsnag_client, 'exceptionHandler']);

    }
  }

}