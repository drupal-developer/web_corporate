<?php


namespace Drupal\logos\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\logos\Entity\Logo;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Bloque logo cabecera.
 *
 * @Block(
 *   id = "block_logo_header",
 *   admin_label = @Translation("Logo cabecera"),
 *   category = @Translation("Logo cabecera"),
 * )
 */
class BlockLogoHeader extends BlockBase {

  /**
   * {@inheritdoc}
   */
  #[ArrayShape(['#theme' => "string", '#url_logo' => "mixed"])]
  public function build(): array {
    $url = NULL;
    $logo = Logo::load(Logo::TYPE_HEADER);
    if ($logo instanceof Logo) {
      $url = $logo->getUrl();
    }

    return [
      '#theme' => 'block_logo_header',
      '#url_logo' => $url,
    ];
  }

}
