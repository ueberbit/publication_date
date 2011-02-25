<?php

/**
 * @file
 * Provide views data and handlers for publication_date.module
 */

function publication_date_views_data() {
  $tables['publication_date']['table']['group'] = t('Node');

  // how is the scheduler table linked to the nodes
  $tables['publication_date']['table']['join']['node'] = array(
    'left_field' => 'nid',
    'field' => 'nid',
  );

  // description of the fields (columns)
  $tables['publication_date']['published_at'] = array(
    'title' => t('Published at'),
    'help' => t('Date/time on which the article has been published'),
    'field' => array(
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ),
    'filter' => array(
      'handler' => 'views_handler_filter_date',
      'label' => t('Published at'),
    ),
    'sort' => array(
      'handler' => 'views_handler_sort_date',
    ),
  );

  return $tables;
}