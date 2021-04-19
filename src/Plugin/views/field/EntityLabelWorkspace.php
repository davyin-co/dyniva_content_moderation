<?php

namespace Drupal\dyniva_content_moderation\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\dyniva_core\TransliterationHelper;
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

/**
 * Field handler to present a link to manage release content.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("entity_label_with_workspace")
 */
class EntityLabelWorkspace extends FieldPluginBase {

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
    $options['no_link'] = ['default' => FALSE];
    $options['use_specified'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['no_link'] = [
      '#title' => $this->t('Not link to entity'),
      '#description' => $this->t('Make entity label a link to entity page.'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['no_link']),
    ];
    $form['use_specified'] = [
      '#title' => $this->t('Use the specified domain name'),
      '#description' => $this->t('Entity links use the specified domain name.'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['use_specified']),
    ];
    parent::buildOptionsForm($form, $form_state);
  }
  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {

    $entity = $this->getEntityTranslation($values);
    if($entity){
      try {
        if (empty($this->options['no_link'])) {
          /**
           * @var ModerationInformation $moderation_info
           */
          $moderation_info = \Drupal::service('content_moderation.moderation_information');
          $last_vid = $moderation_info->getLatestRevisionId($entity->getEntityTypeId(), $entity->id());
          if($moderation_info->isModeratedEntity($entity) && $entity->getRevisionId() != $last_vid && empty($this->options['use_specified'])){
            $url = $entity->urlInfo('latest-version');
          }else{
            $url = $entity->urlInfo();
            //            $url = $entity->urlInfo('revision');
          }
          $request = \Drupal::request();
          if($workspace_id = $request->get('workspace_id',false)){
            $url->setOptions(['query' => ['workspace_id' => $workspace_id]]);
          }
          $options = $url->getOptions();
          $options['attributes']['target'] = '_blank';
          $url->setOptions($options);

          if(!empty($this->options['use_specified'])){
            $config = \Drupal::service('config.factory')->getEditable('ccms_manage.site_info_config');
            $specified = $config->get('specified_domain');
            if($specified){
              $url = Url::fromUri($specified.$url->toString());
            }
          }
          $this->options['alter']['url'] = $url;
          $this->options['alter']['make_link'] = TRUE;
        }
      }
      catch (UndefinedLinkTemplateException $e) {
        $this->options['alter']['make_link'] = FALSE;
      }
      catch (EntityMalformedException $e) {
        $this->options['alter']['make_link'] = FALSE;
      }

      return $this->sanitizeValue($entity->label());
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
