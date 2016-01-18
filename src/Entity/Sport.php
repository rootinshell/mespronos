<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\Sport.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\EntityInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Sport entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "sport",
 *   label = @Translation("Sport"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\SportListController",
 *     "views_data" = "Drupal\mespronos\Entity\SportViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\SportForm",
 *       "add" = "Drupal\mespronos\Entity\Form\SportForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\SportForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\SportDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\SportAccessControlHandler",
 *   },
 *   base_table = "mespronos__sport",
 *   admin_permission = "administer Sport entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/entity.sport.canonical",
 *     "edit-form" = "/entity.sport.edit_form",
 *     "delete-form" = "/entity.sport.delete_form",
 *     "collection" = "/entity.sport.collection"
 *   },
 *   field_ui_base_route = "sport.settings"
 * )
 */
class Sport extends ContentEntityBase implements EntityInterface {
  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += array(
      'creator' => \Drupal::currentUser()->id(),
    );
  }

  /**
   * @param array $values
   * @return Sport
   * @throws \Exception
   */
  public static function create(array $values = array()) {
    if(!isset($values['name']) || empty(trim($values['name']))) {
      throw new \Exception(t('Sport name should not be empty'));
    }
    $query = \Drupal::entityQuery('sport')->condition('name', '%'.$values['name'].'%', 'LIKE');
    $id = $query->execute();
    if (count($id) == 0) {
      return parent::create($values); // TODO: Change the autogenerated stub
    }
    else {
      $sport = entity_load('sport', array_pop($id));
      return $sport;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('updated')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('creator')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('creator')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('creator', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('creator', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Sport entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the Sport entity.'))
      ->setReadOnly(TRUE);

    $fields['creator'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the Sport entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setRequired(TRUE)
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Sport entity.'))
      ->setTranslatable(true)
      ->setRequired(TRUE)
      ->setSettings(array(
        'default_value' => '',
        'max_length' => 50,
        'text_processing' => 0,
      ))
      ->setDisplayOptions('view', array(
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string_textfield',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['updated'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
