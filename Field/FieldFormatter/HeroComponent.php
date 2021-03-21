<?php

namespace Drupal\field_hero\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the 'hero_component_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "hero_component_formatter",
 *   label = @Translation("Hero Component"),
 *   field_types = {
 *     "hero_item"
 *   },
 * )
 */
class HeroComponent extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode = NULL) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $heading = $item->get('heading')->getValue();

      $body = [
        '#type' => 'markup',
        '#markup' => $item->get('body')->getValue()
      ];

      $elements[$delta] = [
        '#theme' => 'block__hero',
        '#content' => [
          'heading' => $heading,
          'body' => $body,
        ],
      ];
    }

    return $elements;
  }

}
