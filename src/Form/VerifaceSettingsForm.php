<?php

declare(strict_types=1);

namespace Drupal\veriface\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure VeriFace settings for this site.
 */
final class VerifaceSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'veriface_veriface_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['veriface.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('VeriFace API key'),
      '#default_value' => $this->config('veriface.settings')->get('api_key'),
    ];

    $form['link_type'] = [
      '#type' => 'select',
      '#title' => $this->t('VeriFace Link Type'),
      '#description' => $this->t('Select type of the verification link.<br>LINK_SHORT - creates a short numerical string to open the verification session valid for 12 hours<br>
LINK_LONG - creates a longer alphanumeric string to open the verification session valid for 72 hours'),
      '#default_value' => $this->config('veriface.settings')->get('link_type') ? $this->config('veriface.settings')->get('link_type') : 'long',
      '#options' => [
        'LINK_SHORT' => 'LINK_SHORT',
        'LINK_LONG' => 'LINK_LONG',
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('veriface.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('link_type', $form_state->getValue('link_type'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
