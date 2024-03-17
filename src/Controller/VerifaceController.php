<?php

namespace Drupal\veriface\Controller;

use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use veriface\VeriFace;

class VerifaceController {
  public function openVerification() {
    $current_user_id = \Drupal::currentUser()->id();
    $current_user = User::load($current_user_id);

    $veriface = VeriFace::byApiKey(\Drupal::config('veriface.settings')->get('api_key'));

    \Drupal::logger('veriface')->debug('Starting verification for user %uid', [
      '%uid' => $current_user_id,
    ]);
    if ($current_user->get('field_veriface')->first()->valid_until === time()) {
      $session_id = $current_user->get('field_veriface')->first()->session_id;
      $open_code = $current_user->get('field_veriface')->first()->open_code;

      \Drupal::logger('veriface')->debug('User %id continue verification with session_id = %session_id', [
        '%id' => $current_user_id,
        '%session_id' => $session_id,
      ]);
    }
    else {
      $created = $veriface->createVerification(\Drupal::config('veriface.settings')->get('link_type'));
      $session_id = $created->sessionId;
      $open_code = $created->openCode;
    }
    $response = new JsonResponse([
        'open_code' => $open_code,
        'session_id' => $session_id,
      ]
    );
    return $response;
  }
}
