<?php

/**
 * @file
 * Builds placeholder replacement tokens for node-related data.
 */

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Language\LanguageInterface;
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
  $tokenService = \Drupal::token();
  $dateFormatter =  \Drupal::service('date.formatter');

  if (isset($options['langcode'])) {
    $langcode = $options['langcode'];
  }
  else {
    $langcode = LanguageInterface::LANGCODE_DEFAULT;
  }
  $replacements = [];

  if ($type == 'node' && !empty($data['node'])) {
    /** @var \Drupal\node\NodeInterface $node */
    $node = $data['node'];

    foreach ($tokens as $name => $original) {
      switch ($name) {
        case 'published':
          $date_format = DateFormat::load('medium');
          $bubbleable_metadata->addCacheableDependency($date_format);
          $replacements[$original] = $dateFormatter->format($node->published_at->value, 'medium', '', NULL, $langcode);
          break;
      }
    }

    if ($published_tokens = $tokenService->findWithPrefix($tokens, 'published')) {
      $replacements += $tokenService->generate('date', $published_tokens, ['date' => $node->published_at->value], $options, $bubbleable_metadata);
    }
  }

  return $replacements;
}
