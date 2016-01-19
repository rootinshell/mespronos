<?php

/**
 * @file
 * Contains Drupal\mespronos\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\mespronos\Entity\Controller\GameController;
use Drupal\mespronos\Entity\Controller\DayController;
use Drupal\mespronos\Entity\Controller\UserInvolveController;
use Drupal\mespronos\Entity\League;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos\Controller
 */
class BettingController extends ControllerBase {
  /**
   * Hello.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Hello World!', [])
    ];
  }

  public function nextBets() {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $days = DayController::getNextDaysToBet(10);
    foreach ($days  as $day_id => $day) {
      $league_id = $day->entity->get('league')->first()->getValue()['target_id'];
      if(!isset($leagues[$league_id])) {
        $leagues[$league_id] = League::load($league_id);
      }
      $league = $leagues[$league_id];
      if(!isset($user_involvements[$league_id])) {
        $user_involvements[$league_id] = UserInvolveController::isUserInvolve($user_uid ,$league_id);
      }
      $day->involve = $user_involvements[$league_id];

      $game_date = \DateTime::createFromFormat('Y-m-d\TH:i:s',$day->day_date);
      $now_date = new \DateTime();

      $i = $game_date->diff($now_date);

      if($day->involve) {
        $action_links = Link::fromTextAndUrl(
          $this->t('Bet now'),
          new Url('mespronos.day.bet', array('day' => $day_id))
        );
      }
      else {
        if($user_uid == 0) {
          if(\Drupal::moduleHandler()->moduleExists(('mespronos_registration'))) {
            $action_links = Link::fromTextAndUrl(
              $this->t('Register or login and start betting'),
              new Url('mespronos_registration.join')
            );
          }
          else {
            $action_links = Link::fromTextAndUrl(
              $this->t('Register or login and start betting'),
              new Url('user.register')
            );
          }
        }
        else {
          $action_links = Link::fromTextAndUrl(
            $this->t('Start betting now !'),
            new Url('mespronos.league.register', array('league' => $league->id()))
          );
        }
      }
      $row = [
        $league->label(),
        $day->entity->label(),
        $day->nb_game,

        $i->format('%a') >0 ? $this->t('@d days, @GH@im',array('@d'=>$i->format('%a'),'@G'=>$i->format('%H'),'@i'=>$i->format('%i'))) : $this->t('@GH@im',array('@G'=>$i->format('%H'),'@i'=>$i->format('%i'))),
        $action_links,
      ];
      $rows[] = $row;
    }
    $header = [
      $this->t('League',array(),array('context'=>'mespronos')),
      $this->t('Day',array(),array('context'=>'mespronos')),
      $this->t('Bets left',array(),array('context'=>'mespronos')),
      $this->t('Time left',array(),array('context'=>'mespronos')),
      '',
    ];
    return [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];
  }

  public function bet($day) {
    $user = \Drupal::currentUser();
    $user_uid =  $user->id();
    $day_storage = \Drupal::entityManager()->getStorage('day');
    $day = $day_storage->load($day);
    if($day === NULL) {
      drupal_set_message($this->t('This day doesn\'t exist.'),'error');
      throw new AccessDeniedHttpException();
    }
    $league_id =$day->get('league')->first()->getValue()['target_id'];
    if(!UserInvolveController::isUserInvolve($user_uid,$league_id)) {
      drupal_set_message($this->t('You\'re not subscribed to this day'),'warning');
      throw new AccessDeniedHttpException();
    }
    $games_to_bet = GameController::getGamesToBet($day);

    $form = \Drupal::formBuilder()->getForm('Drupal\mespronos\Form\GamesBetting',$games_to_bet,$user);
    return $form;

  }

}
