<?php

namespace Drupal\dyniva_content_moderation;

use Drupal\Core\Entity\EntityInterface;
use Drupal\content_moderation\EntityOperations;

/**
 * Defines a class for reacting to entity events.
 *
 * @internal
 */
class DynivaEntityOperations extends EntityOperations {

  /**
   * Acts on an entity and set published status based on the moderation state.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity being saved.
   *
   * @see hook_entity_presave()
   */
  public function entityPresave(EntityInterface $entity) {
    
    if (!$this->moderationInfo->isModeratedEntity($entity)) {
      return;
    }

    if ($entity->moderation_state->value) {
      $workflow = $this->moderationInfo->getWorkflowForEntity($entity);
      /** @var \Drupal\content_moderation\ContentModerationState $current_state */
      $current_state = $workflow->getTypePlugin()
        ->getState($entity->moderation_state->value);

      // This entity is default if it is new, the default revision, or the
      // default revision is not published.
      $update_default_revision = $entity->isNew()
        || $current_state->isDefaultRevisionState()
        || !$this->moderationInfo->isDefaultRevisionPublished($entity);

      if (\Drupal::moduleHandler()->moduleExists('multiversion') && !empty($entity->_deleted->value)){
        $update_default_revision = true;
      }
      // Fire per-entity-type logic for handling the save process.
      $this->entityTypeManager
        ->getHandler($entity->getEntityTypeId(), 'moderation')
        ->onPresave($entity, $update_default_revision, $current_state->isPublishedState());
    }
  }

}
