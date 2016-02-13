<?php

/**
 * @file
 * Contains \Drupal\mespronos_registration\Controller\DefaultController.
 */

namespace Drupal\mespronos\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class DefaultController.
 *
 * @package Drupal\mespronos_registration\Controller
 */
class UserController extends ControllerBase {

  public function passwordReset() {
    if(\Drupal::currentUser()->id() > 0) {
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    $registration_form = self::getResetPasswordForm();
    return $registration_form;
  }

  public function login() {
    if(\Drupal::currentUser()->id() > 0) {
      return new RedirectResponse(\Drupal::url('<front>'));
    }
    $login_form = self::getLoginForm();
    return $login_form;
  }

  public static function getResetPasswordForm() {
    $password_reset_form = \Drupal::formBuilder()
      ->getForm('\Drupal\user\Form\UserPasswordForm');
    unset($password_reset_form['mail']);
    $password_reset_form['actions']['submit']['#value'] = t('Send me password reset instructions');
    return $password_reset_form;
  }

  public static function getLoginForm() {
    $login_form = \Drupal::formBuilder()
      ->getForm('\Drupal\user\Form\UserLoginForm');
    $login_form['name']['#description'] = '';
    $login_form['pass']['#description'] = '';
    $login_form['no_account'] = [
      '#markup' => '<p>'.Link::fromTextAndUrl(
          t('No account ? Register now !'),Url::fromRoute('user.register',[])
        )->toString().'</p>',
      '#weight' => 100,
    ];
    $login_form['password_reset'] = [
      '#markup' => '<p>'.Link::fromTextAndUrl(
          t('Forget your password ?'),Url::fromRoute('mespronos.password-reset',[])
        )->toString().'</p>',
      '#weight' => 101,
    ];
    return $login_form;
  }
}