<?php

/**
 * Implements hook_views_data_alter().
 */
function publication_date_views_data_alter(array &$data) {
  $data['node_field_data']['published_at']['argument']['id'] = 'date';
  $data['node_field_data']['published_at']['filter']['id'] = 'date';
  $data['node_field_data']['published_at']['sort']['id'] = 'date';

  $data['node_field_revision']['published_at']['argument']['id'] = 'date';
  $data['node_field_revision']['published_at']['filter']['id'] = 'date';
  $data['node_field_revision']['published_at']['sort']['id'] = 'date';
}
