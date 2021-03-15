<?php

namespace Drupal\dyniva_content_moderation\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\EntityLabel;
use Drupal\Core\Entity\EntityInterface;
use Drupal\views\Plugin\views\field\Field;
use Drupal\Core\Entity\Exception\UndefinedLinkTemplateException;
use Drupal\Core\Entity\EntityMalformedException;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\ccms_core\Plugin\ManagedEntityPluginManager;

/**
 * Field handler to present a link to manage release content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_revision_link_with_workspace")
 */
class EntityRevisionLink extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query($use_groupby = FALSE) {

  }
  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);

    if($entity){
      $managedEntity = dyniva_core_get_entity_managed_entity($entity);
  		if ($managedEntity && ManagedEntityPluginManager::isPluginEnable($managedEntity, 'revision')) {
        try {
          $url = Url::fromRoute("ccms_core.managed_entity.{$managedEntity->id()}.revision_page",[
            'managed_entity' => $managedEntity->id(),
            'managed_entity_id' => $entity->id(),
            'plugin_id' => 'revision'
          ]);
          $request = \Drupal::request();
          if($workspace_id = $request->get('workspace_id',false)){
            $url->setOptions(['query' => ['workspace_id' => $workspace_id]]);
          }
          $this->options['alter']['url'] = $url;
          $this->options['alter']['make_link'] = TRUE;
        }
        catch (UndefinedLinkTemplateException $e) {
          $this->options['alter']['make_link'] = FALSE;
        }
        catch (EntityMalformedException $e) {
          $this->options['alter']['make_link'] = FALSE;
        }

        return $this->sanitizeValue('Revisions');
      }
    }
    return "";
  }
}
