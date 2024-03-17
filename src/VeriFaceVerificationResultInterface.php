<?php

declare(strict_types=1);

namespace Drupal\veriface;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a verface verification result entity type.
 */
interface VeriFaceVerificationResultInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
