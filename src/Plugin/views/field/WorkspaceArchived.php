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
 * @ViewsField("workspace_archived")
 */
class WorkspaceArchived extends FieldPluginBase {


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
    $archived = $workspace->get('archived')->value;

		return $archived == '0' ? $this->t('Available') : $this->t('Archived');
	}
}
