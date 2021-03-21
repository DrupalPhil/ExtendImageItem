<?php

namespace Drupal\field_hero\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Provides the field type of Symbol.
 *
 * @FieldType(
 *   id = "hero_item",
 *   label = @Translation("Hero Component"),
 *   category = @Translation("Custom"),
 *   default_widget = "hero_component_widget",
 *   default_formatter = "hero_component_formatter",
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}},
 *   cardinality = 1,
 * )
 */
class HeroItem extends ImageItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    // Get the parent field settings
    $settings = parent::defaultFieldSettings();

    // Add the description field to the default image field settings
    $settings['default_content']['heading'] = '';
    $settings['default_content']['body'] = '';

    // Return the updated settings
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    // Get the parent properties
    $properties = parent::propertyDefinitions($field_definition);

    // Add a new property for our heading text field to the field definition
    $properties['heading'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Heading'))
      ->setDescription(new TranslatableMarkup('The main hero heading'))
      ->setRequired(TRUE);

    // Add a new property for our body text field to the field definition
    $properties['body'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Body'))
      ->setDescription(new TranslatableMarkup('Optional body text'));

    // Return our updated property definitions
    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    // Get the parent schema
    $schema = parent::schema($field_definition);

    // Add the database columns for the table
    $schema['columns']['heading'] = [
      'type' => 'varchar',
      'length' => '512',
      'description' => 'The main hero heading.',
    ];

    $schema['columns']['body'] = [
      'type' => 'varchar',
      'length' => '512',
      'description' => 'Optional body text',
    ];

    // Return the schema including our new column
    return $schema;

  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value1 = $this->get('target_id')->getValue();
    $value2 = $this->get('heading')->getValue();
    $value3 = $this->get('body')->getValue();
    return empty($value1) && empty($value2) && empty($value3);
  }

  /**
   * {@inheritdoc}
   */
  public function hasNewEntity() {
    return !$this->isEmpty() && isset($this->entity) && $this->target_id === NULL && $this->entity->isNew();
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultImageForm(array &$element, array $settings) {
    // Modifies the default image form to add the new description field
    // &$ references the variable and modifies it, instead of taking a copy

    // Run the parent class
    parent::defaultImageForm($element, $settings);

    $element['default_content']['heading'] = [
      '#title' => new TranslatableMarkup('Heading'),
      '#type' => 'textfield',
      '#default_value' => isset($settings['default_content']['heading']) ? $settings['default_content']['heading'] : '',
      '#description' => new TranslatableMarkup('The main hero heading.'),
      '#maxlength' => '512',
    ];

    $element['default_content']['body'] = [
      '#title' => new TranslatableMarkup('Body'),
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#default_value' => isset($settings['default_content']['body']) ? $settings['default_content']['body'] : '',
      '#description' => new TranslatableMarkup('Optional body text.'),
      '#maxlength' => '512',
    ];

  }

}
