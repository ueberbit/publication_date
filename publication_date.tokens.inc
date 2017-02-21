<?php

/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function publication_date_token_info_alter(&$data) {
  $data['tokens']['node']['published'] = array(
    'name' => t("Date published"),
    'description' => t("The date the node was published."),
    'type' => 'date',
  );
}

/**
 * Implements hook_tokens().
 */
function publication_date_tokens($type, $tokens, array $data = array(), array $options = array(), BubbleableMetadata $bubbleable_metadata) {
  $url_options = array('absolute' => TRUE);
  if (isset($options['language'])) {
    $url_options['language'] = $options['language'];
    $language_code = $options['language']->language;
  }
  else {
    $language_code = NULL;
  }
  $sanitize = !empty($options['sanitize']);

  $replacements = array();

  if ($type == 'node' && !empty($data['node'])) {
    $node = $data['node'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'published':
          $replacements[$original] = format_date($node->published_at, 'medium', '', NULL, $language_code);
          break;
      }
    }

    if ($published_tokens = \Drupal::token()->findWithPrefix($tokens, 'published')) {
      $replacements += \Drupal::token()->generate('date', $published_tokens, array('date' => $node->published_at), $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
