<?php

namespace Drupal\dyniva_content_moderation\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\LinkBase;
use Drupal\views\ResultRow;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Render archived field.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("workspace_archived_link")
 */
class WorkspaceArchivedLink extends LinkBase {

	/**
	 * {@inheritdoc}
	 */
	protected function getUrlInfo(ResultRow $value) {
		$entity = $this->getEntity($value);
		return Url::fromRoute('workspace_ccms.workspace.archived', ['workspace' => $entity->id()]);
	}

	/**
   * {@inheritdoc}
   */
  protected function renderLink(ResultRow $row) {
  	$this->options['alter']['make_link'] = TRUE;
  	$this->options['alter']['url'] = $this->getUrlInfo($row);
  	$this->addLangcode($row);
  	$entity = $this->getEntity($row);
  	$text = $entity->get('archived')->value ? $this->t('Restore') : $this->t('Archive');
    return $text;
  }

}
