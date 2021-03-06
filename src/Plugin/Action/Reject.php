<?php

namespace Drupal\dyniva_content_moderation\Plugin\Action;

/**
 * Moderate Send For Approve.
 *
 * @Action(
 *   id = "moderate_reject_action",
 *   label = @Translation("Reject")
 * )
 */
class Reject extends ModerateBase {

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, 'need_approve', 'draft');
  }

}

