<?php

declare(strict_types=1);

namespace Drupal\veriface\Plugin\Field\FieldType;

use Dflydev\DotAccessData\Data;
use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Defines the 'veriface_veriface' field type.
 *
 * @FieldType(
 *   id = "veriface_veriface",
 *   label = @Translation("VeriFace"),
 *   description = @Translation("VeriFace field."),
 *   default_widget = "veriface_embed_verification",
 *   default_formatter = "veriface_veriface_field_formatter",
 * )
 */
final class VerifaceType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings(): array {
    $settings = ['veriface_api_key' => ''];
    return $settings + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state): array {
    $element = [];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty(): bool {
    return match ($this->get('session_id')->getValue()) {
      NULL, '' => TRUE,
      default => FALSE,
    };
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {

    // @DCG
    // See /core/lib/Drupal/Core/TypedData/Plugin/DataType directory for
    // available data types.
    $properties['open_code'] = DataDefinition::create('string')
      ->setLabel(t('Open code'))
      ->setRequired(TRUE);
    $properties['session_id'] = DataDefinition::create('string')
      ->setLabel((t('Session ID')));
    $properties['status'] = DataDefinition::create('string')
      ->setLabel(t('Status'));
    $properties['valid_until'] = DataDefinition::create('timestamp')
      ->setLabel(t('Valid until'));
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {

    $columns = [
      'open_code' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'description' => 'Open Code.',
        'length' => 255,
      ],
      'session_id' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'description' => 'Session ID',
        'length' => 255,
      ],
      'status' => [
        'type' => 'varchar',
        'not null' => FALSE,
        'description' => 'Status',
        'length' => 255,
      ],
      'valid_until' => [
        'type' => 'int',
        'not null' => FALSE,
        'description' => 'Validity of the verification session.',
      ],
    ];

    $schema = [
      'columns' => $columns,
      // @todo Add indexes here if necessary.
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition): array {
    $random = new Random();
    $values['value'] = $random->word(mt_rand(1, 50));
    return $values;
  }

}
