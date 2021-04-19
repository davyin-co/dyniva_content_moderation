<?php

namespace Drupal\dyniva_content_moderation\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Render archived field.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("workspace_is_default")
 */
class WorkspaceIsDefault extends FieldPluginBase {


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
    $archived = $workspace->get('default')->value;

		return $archived ? $this->t('Yes') : '';
	}
}
