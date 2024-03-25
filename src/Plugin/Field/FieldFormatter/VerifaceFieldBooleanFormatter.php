<?php

declare(strict_types=1);

namespace Drupal\veriface\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\BooleanFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'VeriFace Field Formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "veriface_field_boolean_formatter",
 *   label = @Translation("VeriFace Boolean value"),
 *   field_types = {"veriface_veriface"},
 * )
 */
final class VerifaceFieldBooleanFormatter extends BooleanFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $status = '';
    $formats = $this->getOutputFormats();

    foreach ($items as $delta => $item) {
      try {
        $status = $item->get('status')->getValue();
      }
      catch (\Exception $e) {
        \Drupal::messenger()->addWarning('Chyba pri záskavaní dá o verifikácii: ' . $e->getMessage());
      }
      $format = $this->getSetting('format');
      $verified = str_starts_with($status, 'VERIFIED');

      if ($format == 'custom') {
        $elements[$delta] = ['#markup' => $verified ? $this->getSetting('format_custom_true') : $this->getSetting('format_custom_false')];
      }
      else {
        $elements[$delta] = ['#markup' => $verified ? $formats[$format][0] : $formats[$format][1]];
      }
    }

    return $elements;
  }
}
