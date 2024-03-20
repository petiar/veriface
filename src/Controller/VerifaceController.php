<?php

namespace Drupal\veriface\Controller;

use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use veriface\Dto\VerificationListDto;
use veriface\VeriFace;

class VerifaceController {

  public function openVerification() {
    $current_user_id = \Drupal::currentUser()->id();
    $current_user = User::load($current_user_id);

    \Drupal::logger('veriface')->debug('Starting verification for user %uid', [
      '%uid' => $current_user_id,
    ]);

    $session_id = $current_user->get('field_veriface')->first()->session_id;
    $veriface = VeriFace::byApiKey(\Drupal::config('veriface.settings')->get('api_key'));

    if ($session_id) {
      // najst ci existuje, ak ano, pouzit, pripadne zistit stav a ak nie, tak vytvorit
      $vf = $veriface->findVerificationBySessionId($session_id);
      // uzivatel uz ma session_id na veriface, nevytvaraj nove
      if ($vf[0] && $vf[0] instanceof VerificationListDto) {
        \Drupal::logger('veriface')->debug('User %id continue verification with session_id = %session_id', [
          '%id' => $current_user_id,
          '%session_id' => $session_id,
        ]);
        $open_code = $current_user->get('field_veriface')->first()->open_code;
      }
      else {
        $created = $veriface->createVerification(\Drupal::config('veriface.settings')->get('link_type'));
        $session_id = $created->sessionId;
        $open_code = $created->openCode;
      }
    }

    $response = new JsonResponse([
        'open_code' => $open_code,
        'session_id' => $session_id,
      ]
    );
    return $response;
  }
}
