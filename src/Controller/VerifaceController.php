<?php

namespace Drupal\veriface\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Drupal\veriface\Verification;
use Symfony\Component\HttpFoundation\JsonResponse;
use veriface\Dto\VerificationListDto;
use veriface\VeriFace;

class VerifaceController extends ControllerBase {
  public function __construct(protected Verification $verification) {

  }
  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    /**
     * @var \Drupal\veriface\Verification $verification
     */
    $verification = $container->get('veriface.verification');
    return new static($verification);
  }

  public function openVerification() {
    $current_user_id = \Drupal::currentUser()->id();
    $current_user = User::load($current_user_id);

    \Drupal::logger('veriface')->debug('Opening verification for user %uid', [
      '%uid' => $current_user_id,
    ]);
    $veriface = VeriFace::byApiKey(\Drupal::config('veriface.settings')->get('api_key'));

    $session_id = $current_user->get('field_veriface')->first()->session_id;

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
        // v databaze mame session_id ale na veriface neexistuje
        $created = $veriface->createVerification(\Drupal::config('veriface.settings')->get('link_type'));
        $session_id = $created->sessionId;
        $open_code = $created->openCode;
      }
    }
    else {
      $created = $veriface->createVerification(\Drupal::config('veriface.settings')->get('link_type'));
      if ($current_user_id > 0) {
        // user is not anonymous but for some reason he does not have session_id stored yet
        $current_user->field_veriface->session_id = $created->sessionId;
        $current_user->field_veriface->open_code = $created->openCode;
        $current_user->save();
      }
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

  public function getVerification(string $session_id): JsonResponse {
    return new JsonResponse($this->verification->getVerification($session_id)[0]);
  }

  public function saveVerification() {
    $session_id = \Drupal::request()->request->get('session_id');
    $status = \Drupal::request()->request->get('status');

    $result = $this->verification->saveVerification($session_id, $status);
    return new JsonResponse(['data' => $result]);
  }
}
