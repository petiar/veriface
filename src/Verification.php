<?php

declare(strict_types=1);

namespace Drupal\veriface;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountProxy;
use Drupal\user\Entity\User;
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
  private VeriFace $veriface;

  private string $session_id;
  /**
   * Constructs a Verification object.
   */
  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    private readonly AccountProxy $account
  ) {
    $this->veriface = VeriFace::byApiKey($this->configFactory->get('veriface.settings')->get('api_key'));
    $user = User::load($this->account->id());
    $this->session_id = $user->get('field_veriface')->first()->session_id;
  }

  /**
   * Finds out whether user can continue with verification
   */
  public function isVerificationFinished(): bool {
    $result = FALSE;
    $vf = $this->veriface->findVerificationBySessionId($this->session_id);
    if (isset($vf[0]) && $vf[0] instanceof VerificationListDto) {
      $result = in_array($vf[0]->status, self::END_STATES);
    }
    return $result;
  }

  public static function create(ContainerInterface $container) {
    return new static([
      $container->get('config.factory'),
      $container->get('current_user'),
    ]);
  }

}
