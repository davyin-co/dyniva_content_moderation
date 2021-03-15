<?php

use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Implements hook_views_data().
 */
function dyniva_content_moderation_views_data_alter(array &$data) {
  $data['node']['link_to_moderate'] = [
    'field' => [
      'title' => t('Link to moderate'),
      'id' => 'node_moderate_link',
      'click sortable' => FALSE,
    ]
  ];

  $data['node']['link_to_revisions'] = [
    'field' => [
      'title' => t('Link to revisions'),
      'id' => 'node_revisions_link',
      'click sortable' => FALSE,
    ]
  ];

  $data['node']['link_to_edit'] = [
    'field' => [
      'title' => t('Link to Edit'),
      'id' => 'node_edit_link',
      'click sortable' => FALSE,
    ]
  ];
  $data['content_moderation_state_field_data']['moderation_state']['field']['id'] = 'content_moderation_state_label';
  $data['content_moderation_state_field_data']['moderation_state']['filter']['id'] = 'content_moderation_state_select';
  $data['content_moderation_state_field_revision']['moderation_state']['filter']['id'] = 'content_moderation_state_select';

  $entity_types_with_moderation = array_filter(\Drupal::entityTypeManager()->getDefinitions(), function (EntityTypeInterface $type) {
    return \Drupal::service('content_moderation.moderation_information')->canModerateEntitiesOfEntityType($type);
  });

  $content_moderation_state_entity_type = \Drupal::entityTypeManager()->getDefinition('content_moderation_state');
  $content_moderation_state_entity_base_table = $content_moderation_state_entity_type->getDataTable() ?: $content_moderation_state_entity_type->getBaseTable();
  $content_moderation_state_entity_revision_base_table = $content_moderation_state_entity_type->getRevisionDataTable() ?: $content_moderation_state_entity_type->getRevisionTable();
  foreach ($entity_types_with_moderation as $entity_type_id => $entity_type) {
    $table = $entity_type->getDataTable() ?: $entity_type->getBaseTable();

    $data[$table]['moderation_state']['relationship']['extra'][] = [
      'field' => 'content_entity_revision_id',
      'left_field' => $entity_type->getKey('revision'),
    ];
    $revision_table = $entity_type->getRevisionDataTable() ?: $entity_type->getRevisionTable();
    $data[$revision_table]['moderation_state']['extra'][] = [
      'field' => 'content_entity_revision_id',
      'left_field' => $entity_type->getKey('revision'),
    ];
    if($entity_type->hasKey('langcode')){
      $data[$table]['moderation_state']['relationship']['extra'][] = [
        'field' => $content_moderation_state_entity_type->getKey('langcode'),
        'left_field' => $entity_type->getKey('langcode'),
      ];
      $data[$revision_table]['moderation_state']['relationship']['extra'][] = [
        'field' => $content_moderation_state_entity_type->getKey('langcode'),
        'left_field' => $entity_type->getKey('langcode'),
      ];
    }
  }

  $data['node_field_data']['title']['field']['id'] = 'entity_label_with_workspace';
  $data['node_field_revision']['title']['field']['id'] = 'entity_label_with_workspace';

  $data['node']['entity_revision_link_with_workspace'] = [
    'field' => [
      'title' => t('Entity revision page link'),
      'id' => 'entity_revision_link_with_workspace',
      'click sortable' => FALSE,
    ]
  ];
  $data['node']['entity_current_state'] = [
    'field' => [
      'title' => t('Entity current state'),
      'id' => 'entity_current_state',
      'click sortable' => FALSE,
    ]
  ];
  $data['workspace']['is_default'] = [
    'title' => t('Default'),
    'field' => [
      'title' => t('Default'),
      'help' => t('Is default workspace.'),
      'id' => 'workspace_is_default',
      'click sortable' => FALSE,
    ]
  ];
  $data['workspace']['desc'] = [
    'title' => t('Description'),
    'field' => [
      'title' => t('Description'),
      'help' => t('The description of the workspace.'),
      'id' => 'workspace_description',
      'click sortable' => FALSE,
    ]
  ];
}
