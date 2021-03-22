<?php

namespace Drupal\field_hero\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Render\ElementInfoManagerInterface;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Plugin implementation of the 'hero_component_widget' widget.
 *
 * @FieldWidget(
 *   id = "hero_component_widget",
 *   label = @Translation("Hero Widget"),
 *   field_types = {
 *     "hero_item"
 *   }
 * )
 */
class HeroComponentWidget extends ImageWidget {

  /**
   * {@inheritdoc}
   */
  public static function process($element, FormStateInterface $form_state, $form) {
    $item = $element['#value'];

    $element['heading'] = [
      '#title' => t('Heading'),
      '#type' => 'textfield',
      '#default_value' => isset($item['heading']) ? $item['heading'] : '',
      '#description' => t('The main hero heading.'),
      '#access' => (bool) true,
      '#maxlength' => '512',
      '#weight' => '10',
    ];

    $element['body'] = [
      '#title' => t('Body'),
      '#type' => 'text_format',
      '#format' => 'rich_text',
      '#default_value' => isset($item['body']) ? $item['body'] : '',
      '#description' => t('Optional body text.'),
      '#access' => (bool) true,
      '#maxlength' => '512',
      '#weight' => '10',
    ];

    return parent::process($element, $form_state, $form);
  }

  /**
   * {@inheritdoc}
   *
   * Pretty much everything copied from parent but the parent clears values on
   * ajax callback so, here, we don't clear those values.
   *
   * @see \Drupal\file\Plugin\Field\FieldWidget::submit
   */
  public static function submit($form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $parents = array_slice($button['#parents'], 0, -2);
    NestedArray::setValue($form_state->getUserInput(), $parents, NULL);

    // Go one level up in the form, to the widgets container.
    $element = NestedArray::getValue($form, array_slice($button['#array_parents'], 0, -1));
    $field_name = $element['#field_name'];
    $parents = $element['#field_parents'];

    $submitted_values = NestedArray::getValue($form_state->getValues(), array_slice($button['#parents'], 0, -2));

    // If there are more files uploaded via the same widget, we have to separate
    // them, as we display each file in its own widget.
    $new_values = [];
    foreach ($submitted_values as $delta => $submitted_value) {

      $i = 2;
      if (is_array($submitted_value['fids'])) {
        foreach ($submitted_value['fids'] as $fid) {
          $new_value = $submitted_value;
          $new_value['fids'] = [$fid];
          $new_value['body'] = $submitted_value["body"]["value"];
          $new_values[] = $new_value;
        }
      }
      else {
        $new_value = $submitted_value;
        $new_value['body'] = $submitted_value["body"]["value"];
        $new_values[] = $new_value;
      }
    }

    // Re-index deltas after removing empty items.
    $submitted_values = array_values($new_values);

    // Update form_state values.
    NestedArray::setValue($form_state->getValues(), array_slice($button['#parents'], 0, -2), $submitted_values);

    // Update items.
    $field_state = static::getWidgetState($parents, $field_name, $form_state);
    $field_state['items'] = $submitted_values;
    static::setWidgetState($parents, $field_name, $form_state, $field_state);
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {

    if(!empty($values)) {
      foreach ($values as $key => $value) {
        if(!empty($value["body"])) {
          $values[$key]["body"] = $value["body"]['value'];
        }
      }
    }

    return parent::massageFormValues($values, $form, $form_state);
  }

}
