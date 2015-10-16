<?php

/**
 * @file
 * Contains Drupal\mespronos\Entity\League.
 */

namespace Drupal\mespronos\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\mespronos\LeagueInterface;
use Drupal\user\UserInterface;

/**
 * Defines the League entity.
 *
 * @ingroup mespronos
 *
 * @ContentEntityType(
 *   id = "league",
 *   label = @Translation("League"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\mespronos\Entity\Controller\LeagueListController",
 *     "views_data" = "Drupal\mespronos\Entity\LeagueViewsData", *
 *     "form" = {
 *       "default" = "Drupal\mespronos\Entity\Form\LeagueForm",
 *       "add" = "Drupal\mespronos\Entity\Form\LeagueForm",
 *       "edit" = "Drupal\mespronos\Entity\Form\LeagueForm",
 *       "delete" = "Drupal\mespronos\Entity\Form\LeagueDeleteForm",
 *     },
 *     "access" = "Drupal\mespronos\LeagueAccessControlHandler",
 *   },
 *   base_table = "mespronos__league",
 *   admin_permission = "administer League entity",
 *   fieldable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/entity.league.canonical",
 *     "edit-form" = "/entity.league.edit_form",
 *     "delete-form" = "/entity.league.delete_form",
 *     "collection" = "/entity.league.collection"
 *   },
 *   field_ui_base_route = "league.settings"
 * )
 */
class League extends ContentEntityBase implements LeagueInterface {
  protected static $status_allowed_value = [
    'future' => 'À venir',
    'active' => 'En cours',
    'over' => 'Terminé',
    'archived' => 'Archivé',
  ];
  protected static $betting_types = [
    'score' => 'Score',
    'winner' => '1N2',
  ];
  protected static $status_default_value = 'active';

  public static function load($id) {
    $storage = \Drupal::entityManager()->getStorage('league');
    $entity = $storage->loadMultiple(array($id));
    return array_pop($entity);
  }

  public function getStatus($asMachineName = false) {
    $s = $this->get('status')->value;
    if($asMachineName) {
      return $s;
    }
    else {
      return self::$status_allowed_value[$s];
    }
  }

  public function getBettingType($asMachineName = false) {
    $s = $this->get('betting_type')->value;
    if($asMachineName) {
      return $s;
    }
    else {
      return self::$betting_types[$s];
    }
  }


  public function HasClassement() {
    return $this->get('classement')->value;
  }

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
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getupdatedTime() {
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

  public function getSport() {
    $sport = entity_load('sport', $this->get('sport')->target_id);
    return $sport;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the League entity.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The UUID of the League entity.'))
      ->setReadOnly(TRUE);

    $fields['creator'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of the League entity author.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\node\Entity\Node::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);


    $fields['sport'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Sport'))
      ->setDescription(t('Sport entity reference'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'sport')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'entity_reference',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ),
        'weight' => -3,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Nom'))
      ->setDescription(t('Nom de la compétition.'))
      ->setTranslatable(TRUE)
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
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    //Création d'un champ booléen avec un widget checkbox
    $fields['classement'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Classement activé'))
      ->setDescription(t('Doit-on calculer le classement entre les équipes pour cette competitions'))
      //est-ce que l'on autorise les modifications d'affichage dans le formulaire
      ->setDisplayConfigurable('form', TRUE)
      //est-ce que l'on autorise les modifications d'affichage en frontoffice
      ->setDisplayConfigurable('view', TRUE)
      //définition de la valeur par défaut
      ->setDefaultValue(TRUE)
      //définition des options d'affichage par défaut (front => view, back => form)
      ->setDisplayOptions('form', array(
        //on veut une checkbox
        'type' => 'boolean_checkbox',
        'weight' => - 4,
        'settings' => array(
          'display_label' => TRUE,
        )
      ))
      ->setDisplayOptions('view', array('type' => 'hidden'));

    //Création d'une propriété "liste de texte"
    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Statut de la compétition'))
      ->setRequired(true)
      ->setSettings(array(
        //définition des valeurs possible
        'allowed_values' => self::$status_allowed_value,
      ))
      //définition de la valeur par défaut
      ->setDefaultValue(self::$status_default_value)
      ->setDisplayOptions('view', array(
        'type' => 'hidden',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['betting_type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Betting type'))
      ->setRequired(true)
      ->setSettings(array(
        //définition des valeurs possible
        'allowed_values' => self::$betting_types,
      ))
      //définition de la valeur par défaut
      ->setDisplayOptions('view', array(
        'type' => 'hidden',
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => -4,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['points_score_found'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Points when the game\'s score is found'))
      ->setRequired(true)
      ->setDefaultValue(10)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array('type' => 'hidden'))
      ->setDisplayOptions('form', array('type' => 'number'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['points_winner_found'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Points when the game\'s winner is found'))
      ->setRequired(true)
      ->setDefaultValue(5)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array('type' => 'hidden'))
      ->setDisplayOptions('form', array('type' => 'number'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['points_participation'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Points when nothing is right.'))
      ->setRequired(true)
      ->setDefaultValue(1)
      ->setSetting('unsigned', TRUE)
      ->setDisplayOptions('view', array('type' => 'hidden'))
      ->setDisplayOptions('form', array('type' => 'number'))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['updated'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('updated'))
      ->setDescription(t('The time that the entity was last edited.'));

    return $fields;
  }

}
