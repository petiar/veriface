<?php

declare(strict_types=1);

namespace Drupal\veriface;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\User;
use Drupal\veriface\Entity\VeriFaceVerificationResult;
use Symfony\Component\DependencyInjection\ContainerInterface;
use veriface\Dto\VerificationListDto;
use veriface\VeriFace;

/**
 * @todo Add class description.
 */
final class Verification implements ContainerInjectionInterface {
  const END_STATES = [
    'VERIFIED',
    'REFUSED',
    'VERIFIED_WARNING',
    'CANCELLED',
    'VERIFIED_MANUAL',
    'REFUSED_MANUAL',
    'EXPIRED',
    'ERROR',
  ];

  const STATUSES = [
    'NEW' => 'Novo vytvorené overenie, overovací proces ešte nebol spustený',
    'WAITING_ENDUSER' => 'Overovací proces bol spustený a čaká sa na dokončenie',
    'VERIFIED' => 'Overený',
    'REFUSED' => 'Výsledok overovacieho procesu je: <strong>Zamietnutý</strong>',
    'VERIFIED_WARNING' => 'Overený s upozornením',
    'PARTIALLY_VERIFIED' => 'Čiastočne overený',
    'CANCELLED' => 'Overovací proces bol zrušený overovanou osobu.',
    'VALIDATION_NEEDED' => 'Výsledok overovacieho procesu nie je jednoznačný a je ho nutné ručne potvrdiť',
    'VERIFIED_MANUAL' => 'Ručne potvrdený výsledok so stavom <strong>Overený</strong>',
    'REFUSED_MANUAL' => 'Ručne potvrdený výsledok so stavom <strong>Zamietnutý</strong>',
    'ACTION_NEEDED' => 'Je nutný zásah oprávnenej osoby (napr. pri zlej detekcií údajov, po manuálnej oprave oprávnenej osoby).',
    'ERROR' => 'Chyba počas overovacieho procesu',
    'WAITING' => 'Čaká sa na výsledné vyhodnotenie systémom',
    'UNKNOWN' => 'Neznámy výsledok',
    'EXPIRED' => 'Overovací proces exspiroval, tj. overovaná osoba ho neotvorila včas, alebo nedokončila v stanovenom čase.',
  ];
  private VeriFace $veriface;

  private string $session_id;
  /**
   * Constructs a Verification object.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    private AccountProxy $account
  ) {
    $this->veriface = VeriFace::byApiKey($this->configFactory->get('veriface.settings')->get('api_key'));
    $user = User::load($this->account->id());
      $this->session_id = isset($user->get('field_veriface')
          ->first()->session_id) ? $user->get('field_veriface')
        ->first()->session_id : '';
  }

  public function getSessionId() {
    return $this->session_id;
  }

  /**
   * Finds out whether user can continue with verification
   */
  public function getVerificationStatus(): array|null {
    $result = null;
    if ($this->session_id) {
      $vf = $this->veriface->findVerificationBySessionId($this->session_id);
      if (isset($vf[0]) && $vf[0] instanceof VerificationListDto) {
        $result = $vf[0]->status;
      }
      $result = [
        'machine' => $result,
        'human' => self::STATUSES[$result],
      ];
    }
    return $result;
  }

  public static function create(ContainerInterface $container) {
    return new static([
      $container->get('config.factory'),
      $container->get('current_user'),
    ]);
  }

  public function getVerification($session_id) {
    return $this->veriface->findVerificationBySessionId($session_id);
  }

  public function saveVerification($session_id, $status) {
    $data = [];
    $query = \Drupal::entityQuery('user');
    $query->accessCheck(TRUE);
    $query->condition('field_veriface.session_id', $session_id);
    $results = $query->execute();

    if ($results) {
      $user = User::load(array_keys($results)[0]);
      $vf = $this->veriface->getVerification($session_id);

      $user->field_veriface->status = $vf->status;
      $user->save();

      if (str_starts_with($vf->status, 'VERIFIED')) {
        veriface_add_role_to_user($user);
      }

      $user->field_veriface->status = $vf->status;

      $save = veriface_create_entity($session_id, $vf);
      $data = [
        'status' => $vf->status,
        'status_human' => self::STATUSES[$vf->status],
        'saved' => TRUE,
      ];
    }
    return $data;
  }
}
