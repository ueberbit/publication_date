<?php

/**
 * @file
 * Contains \Drupal\publication_date\Plugin\Field\FieldWidget\TimestampDatetimeWidget.
 */

namespace Drupal\publication_date\Plugin\Field\FieldWidget;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Datetime\Element\Datetime;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'datetime timestamp' widget.
 *
 * @FieldWidget(
 *   id = "publication_date_timestamp",
 *   label = @Translation("Datetime Timestamp"),
 *   field_types = {
 *     "published_at"
 *   }
 * )
 */
class TimestampDatetimeWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $date_format = DateFormat::load('html_date')->getPattern();
    $time_format = DateFormat::load('html_time')->getPattern();
    if (isset($items[$delta]->value) && $items[$delta]->value != PUBLICATION_DATE_DEFAULT) {
      $default_value = DrupalDateTime::createFromTimestamp($items[$delta]->value);
    }
    else {
      $default_value = '';
    }
    $element['value'] = $element + array(
      '#type' => 'datetime',
      '#default_value' => $default_value,
      '#date_year_range' => '1902:2037',
    );
    $element['value']['#description'] = $this->t('Format: %format. Leave blank to use the time of form submission.', array('%format' => Datetime::formatExample($date_format . ' ' . $time_format)));

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$item) {
      $date = NULL;
      // @todo The structure is different whether access is denied or not, to
      //   be fixed in https://www.drupal.org/node/2326533.
      if (isset($item['value']) && $item['value'] instanceof DrupalDateTime) {
        $date = $item['value'];
      }
      elseif (isset($item['value']['object']) && $item['value']['object'] instanceof DrupalDateTime) {
        $date = $item['value']['object'];
      }
      if ($date) {
        $item['value'] = $date->getTimestamp();
      }
    }
    return $values;
  }

}
