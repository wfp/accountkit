<?php

namespace Drupal\accountkit\Authentication\Provider;

use Drupal\accountkit\AccountKitManager;
use Drupal\Core\Authentication\AuthenticationProviderInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccountKitAuthenticationProvider.
 *
 * @package Drupal\accountkit\Authentication\Provider
 */
class AccountKitAuthenticationProvider implements AuthenticationProviderInterface {

  /**
   * The Account Kit manager.
   *
   * @var \Drupal\accountkit\AccountKitManager
   */
  protected $accountKitManager;

  /**
   * Constructs a HTTP basic authentication provider object.
   *
   * @param \Drupal\accountkit\AccountKitManager $accountkit_manager
   */
  public function __construct(AccountKitManager $accountkit_manager) {
    $this->accountKitManager = $accountkit_manager;
  }

  /**
   * Checks whether suitable authentication credentials are on the request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return bool
   *   TRUE if authentication credentials suitable for this provider are on the
   *   request, FALSE otherwise.
   */
  public function applies(Request $request) {
    // Check for the presence of the code.
    if (\Drupal::request()->get('code')) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate(Request $request) {
    $user = NULL;
    $data = $this->accountKitManager->getUserInfo();
    if (!empty($data['email']['address'])) {
      $user = user_load_by_mail($data['email']['address']);
      if ($user) {
        drupal_set_message("You are now logged in as " . $user->getDisplayName(), "success");
      }
      else {
        $user = User::create();
        $user->setPassword('password');
        $user->enforceIsNew();
        $user->setEmail($data['email']['address']);
        $user->setUsername($data['email']['address']);
        $user->activate();
        $user->save();

        drupal_set_message("User successfully created!", "success");
        drupal_set_message("You are now logged in as " . $user->getDisplayName(), "success");
      }

      user_login_finalize($user);

    }

    return $user;
  }

}
