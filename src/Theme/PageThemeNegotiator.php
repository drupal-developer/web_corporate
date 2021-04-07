<?php


namespace Drupal\corporate\Theme;


use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\user\Entity\User;

class PageThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * The proxy to the current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected AccountProxyInterface $account;

  /**
   * PageThemeNegotiator constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   */
  public function __construct(AccountProxyInterface $account) {
    $this->account = $account;
  }


  /**
   * @inheritDoc
   */
  public function applies(RouteMatchInterface $route_match): bool {
    $apply = FALSE;
    if ($route_match->getRouteName() == 'user.reset') {
      $apply = TRUE;
    }

    return $apply;
  }

  /**
   * @inheritDoc
   */
  public function determineActiveTheme(RouteMatchInterface $route_match): ?string {
    return 'gin';
  }
}
