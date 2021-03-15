<?php

namespace Drupal\dyniva_content_moderation\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Render description field.
 *
 * @ingroup views_field_handles
 *
 * @ViewsField("workspace_description")
 */
class WorkspaceDescription extends FieldPluginBase {

	/**
   * {@inheritdoc}
   */
	public function query() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function render(ResultRow $value) {
		$workspace = $value->_entity;
		return $workspace->get('description')->value;
	}
}
