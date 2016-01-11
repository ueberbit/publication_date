<?php

/**
 * @file
 * Contains \Drupal\publication_date\PublicationDateNodePermissions.
 */

namespace Drupal\publication_date;

use Drupal\node\Entity\NodeType;
use Drupal\node\NodePermissions;

class PublicationDateNodePermissions extends NodePermissions {

  protected function buildPermissions(NodeType $type) {
    $type_id = $type->id();
    $type_params = array('%type_name' => $type->label());

    return [
      "set $type_id published on date" => array(
        'title' => $this->t('Modify %type_name "Published On" date.', $type_params),
        'description' => $this->t('Change the "Published On" date for this content type.'),
      ),
    ];
  }

}
