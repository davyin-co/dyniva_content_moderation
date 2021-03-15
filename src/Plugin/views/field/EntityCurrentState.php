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
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\views\Entity\Render\EntityTranslationRenderTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\content_moderation\ModerationInformation;
use Drupal\workflows\WorkflowInterface;
use Drupal\dyniva_core\TransliterationHelper;
use Drupal\Core\Link;

/**
 * Field handler to present a link to manage release content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_current_state")
 */
class EntityCurrentState extends FieldPluginBase {

  use EntityTranslationRenderTrait;
  /**
   * {@inheritdoc}
   */
  public function query($use_groupby = FALSE) {

  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['make_link'] = ['default' => TRUE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['make_link'] = [
      '#title' => $this->t('link to entity current version'),
      '#description' => $this->t('Make a link to entity current version page.'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['make_link']),
    ];
    parent::buildOptionsForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $entity = $this->getEntityTranslation($values);
    if($entity){
      /**
       * @var ModerationInformation $moderation_info
       */
      $moderation_info = \Drupal::service('content_moderation.moderation_information');
      $url = false;
      try {
        if (!empty($this->options['make_link'])) {
          if($moderation_info->hasPendingRevision($entity) ){
            $url = $entity->urlInfo('latest-version');

          }elseif (!$entity->isPublished()){
            $url = $entity->urlInfo('revision');
          }

//           $this->options['alter']['url'] = $url;
//           $this->options['alter']['make_link'] = TRUE;
        }
      }
      catch (UndefinedLinkTemplateException $e) {
      }
      catch (EntityMalformedException $e) {
      }

      /**
       *
       * @var WorkflowInterface $workflow
       */
      $workflow = $moderation_info->getWorkflowForEntity($entity);
      $moderation_state = $entity->moderation_state->value;
      if($workflow && !empty($moderation_state)){
        $state = $workflow->getTypePlugin()->getState($moderation_state);

        $output = t($state->label())->render();
        if($url){
          $request = \Drupal::request();
          if($workspace_id = $request->get('workspace_id',false)){
            $url->setOptions(['query' => ['workspace_id' => $workspace_id]]);
          }
          $options = $url->getOptions();
          $options['attributes']['target'] = '_blank';
          $url->setOptions($options);

          $link = Link::fromTextAndUrl(t('Preview'),$url)->toString();
          $output .= "({$link})";
        }
        return $this->sanitizeValue($output,'xss');
      }
    }
    return "";
  }
  /**
   *
   * @param EntityInterface $entity
   * @param ResultRow $row
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public function getEntityTranslation(ResultRow $row) {
    // We assume the same language should be used for all entity fields
    // belonging to a single row, even if they are attached to different entity
    // types. Below we apply language fallback to ensure a valid value is always
    // picked.
    $translation = $entity = $this->getEntity($row);
    if ($entity instanceof TranslatableInterface) {
      $langcode = $entity->get('langcode')->value;
      if (isset($row->node_field_data_langcode)) {
        $langcode = $row->node_field_data_langcode;
      }
      if($entity = TransliterationHelper::getLatestTranslationAffectedRevision($entity, $langcode)) {
        $translation = $this->getEntityManager()->getTranslationFromContext($entity, $langcode, ['operation' => 'entity_upcast']);
      }
    }
    return $translation;
  }
  /**
   * {@inheritdoc}
   */
  public function getEntityTypeId() {
    return 'node';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityManager() {
    return \Drupal::entityManager();
  }

  /**
   * {@inheritdoc}
   */
  protected function getLanguageManager() {
    return \Drupal::languageManager();
  }

  /**
   * {@inheritdoc}
   */
  protected function getView() {
    return $this->view;
  }
}
