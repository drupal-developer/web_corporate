<?php


namespace Drupal\logos\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\logos\Entity\Logo;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Bloque logo footer.
 *
 * @Block(
 *   id = "block_logo_footer",
 *   admin_label = @Translation("Logo pie"),
 *   category = @Translation("Logo pie"),
 * )
 */
class BlockLogoFooter extends BlockBase {

  /**
   * {@inheritdoc}
   */
  #[ArrayShape(['#theme' => "string", '#url_logo' => "mixed"])]
  public function build(): array {
    $url = NULL;
    $logo = Logo::load(Logo::TYPE_FOOTER);
    if ($logo instanceof Logo) {
      $url = $logo->getUrl();
    }

    return [
      '#theme' => 'block_logo_footer',
      '#url_logo' => $url,
    ];
  }

}
