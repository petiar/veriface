<?php

declare(strict_types=1);

namespace Drupal\veriface\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\veriface\Verification;

/**
 * Defines the 'veriface_embed_verification' field widget.
 *
 * @FieldWidget(
 *   id = "veriface_embed_verification",
 *   label = @Translation("VeriFace"),
 *   field_types = {
 *     "veriface_veriface"
 *   },
 * )
 */
final class VerifaceWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $setting = [];
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
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state): array {
    $element += [
      '#type' => 'fieldset',
      '#title' => $this->t('Veriface - overenie totožnosti'),
    ];

    $element['#attached']['library'][] = 'veriface/embed-verification';

    $element['open_code'] = [
      '#type' => 'hidden',
      '#default_value' => $items[$delta]->open_code ?? NULL,
      '#attributes' => [
        'id' => 'veriface_open_code',
      ],
    ];

    $element['session_id'] = [
      '#type' => 'hidden',
      '#default_value' => $items[$delta]->session_id ?? NULL,
      '#attributes' => [
        'id' => 'veriface_session_id',
      ],
    ];

    $element['status'] = [
      '#type' => 'hidden',
      '#default_value' => $items[$delta]->status ?? NULL,
    ];

    /**
     * @var Verification $verification
     */
    $verification = \Drupal::service('veriface.verification');
    $status = $verification->getVerificationStatus();
    if (in_array($status['machine'], $verification::END_STATES)) {
      // we have terminal status, we will display only a message without button
      switch ($status['machine']) {
        case 'VERIFIED':
        case 'VERIFIED_WARNING':
        case 'VERIFIED_MANUAL':
          $message = 'Overenie bolo úspešné. Stav: ' . $status['human'];
          break;
        default:
          $message = 'Pri overovaní nastali problémy. ' . $status['human'] . ' Kontaktuje administrátora a uveďte toto číslo: ' . $verification->getSessionId();
      }
      $element['stop'] = [
        '#type' => 'item',
        '#markup' => $message,
      ];
    }
    else {
      $element['modal'] = [
        '#type' => 'item',
        '#description' => 'Po kliknutí na toto tlačidlo budete požiadaný o overenie totožnosti. Celý proces prebieha vo Vašom prehliadači.',
        '#markup' => '<div id="verification-messages"></div><div class="button" id="openVerificationModal">Overiť totožnosť</div><div id="embed-dialog"></div>',
      ];
    }
    return $element;
  }

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    if (\Drupal::config('veriface.settings')->get('link_type') === 'LINK_LONG') {
      $validity = 72 * 60 * 60;
    }
    else {
      $validity = 12 * 60 * 60;
    }
    $values[0]['valid_until'] = time() + $validity;
    return parent::massageFormValues($values, $form, $form_state);
  }
}
