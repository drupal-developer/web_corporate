<?php


namespace Drupal\corporate\Taxonomy;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Loads taxonomy terms in a tree
 */
class TaxonomyTermTree {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * TaxonomyTermTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Loads the tree of a vocabulary.
   *
   * @param string $vocabulary
   *   Machine name
   *
   * @return array
   */
  public function load(string $vocabulary): array {
    $terms = [];
    try {
      $terms = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadTree($vocabulary);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('corporate')->error($e->getMessage());
    }
    $tree = [];
    foreach ($terms as $tree_object) {
      $this->buildTree($tree, $tree_object, $vocabulary);
    }

    return $tree;
  }

  /**
   * Populates a tree array given a taxonomy term tree object.
   *
   * @param $tree
   * @param $object
   * @param $vocabulary
   */
  protected function buildTree(&$tree, $object, $vocabulary) {
    if ($object->depth != 0) {
      return;
    }
    $tree[$object->tid] = $object;
    $tree[$object->tid]->children = [];
    $object_children = &$tree[$object->tid]->children;
    $children = NULL;
    try {
      $children = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadChildren($object->tid);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('corporate')->error($e->getMessage());
    }
    if (!$children) {
      return;
    }
    $child_tree_objects = [];
    try {
      $child_tree_objects = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadTree($vocabulary, $object->tid);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      \Drupal::logger('corporate')->error($e->getMessage());
    }

    foreach ($children as $child) {
      foreach ($child_tree_objects as $child_tree_object) {
        if ($child_tree_object->tid == $child->id()) {
          $this->buildTree($object_children, $child_tree_object, $vocabulary);
        }
      }
    }
  }
}
