<?php


namespace Drupal\redes\Plugin\Block;


use Drupal\Core\Block\BlockBase;
use Drupal\file\Entity\File;
use Drupal\redes\Entity\RedSocial;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Bloque redes sociales cabecera.
 *
 * @Block(
 *   id = "block_red_social_header",
 *   admin_label = @Translation("Redes sociales cabecera"),
 *   category = @Translation("Redes sociales cabecera"),
 * )
 */
class BlockRedSocialHeader extends BlockBase {

  /**
   * {@inheritdoc}
   */
  #[ArrayShape(['#theme' => "string", '#redes' => "array"])]
  public function build(): array {
    $redes = [];

    $query = \Drupal::entityQuery('red_social');
    $result = $query->execute();
    foreach ($result as $id) {
      /** @var RedSocial $red */
      $red = RedSocial::load($id);
      $file = File::load($red->get('icono')->target_id);
      $redes[$id] = [
        'url' => $red->get('url')->value,
        'icono' => $file
      ];
    }


    return [
      '#theme' => 'block_red_social_footer',
      '#redes' => $redes,
    ];
  }

}
