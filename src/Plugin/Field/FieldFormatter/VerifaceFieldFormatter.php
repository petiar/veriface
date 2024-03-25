<?php

declare(strict_types=1);

namespace Drupal\veriface\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'VeriFace Field Formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "veriface_veriface_field_formatter",
 *   label = @Translation("VeriFace status"),
 *   field_types = {"veriface_veriface"},
 * )
 */
final class VerifaceFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $setting = ['foo' => 'bar'];
    return $setting + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    return [
      $this->t('This formatter has no settings.'),
    ];
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    /**
     * @var \Drupal\veriface\Verification $verification
     */
    $verification = \Drupal::service('veriface.verification');
    $status = null;
    $element = [];
    /**
     * @var \Drupal\veriface\Plugin\Field\FieldType\VerifaceType $item
     */
    foreach ($items as $delta => $item) {
      try {
        $status = $item->get('status')->getValue();
      }
      catch (\Exception $e) {
        \Drupal::messenger()->addWarning('Chyba pri záskavaní dá o verifikácii: ' . $e->getMessage());
      }
      $element[$delta] = [
        '#markup' => $verification::STATUSES[$status],
      ];
    }
    return $element;
  }

}
