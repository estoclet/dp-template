<?php
namespace Drupal\icdc_blog\Twig;
use Drupal\block\Entity\Block;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;


class IcdcBlogTermName extends \Twig_Extension {

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('icdc_blog_name_url', array($this, 'termNameToUrl'))
    ];
  }

  /**
   * In this function we can declare the extension function.
   */
  public function getFunctions()
  {
    return array(
      new \Twig_SimpleFunction('blogAuteurSecondaire', array($this, 'blogAuteurSecondaire'), array('is_safe' => array('html'))),
    );
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'icdc_blog.twig_extension';
  }

  /**
   * Add your special caractere to string
   */
  public static function termNameToUrl($string) {
    $ret = preg_replace('/\W/ui', '-', $string);
    return mb_strtolower($ret);
  }

  /**
   * Add AuteurSecondaire To blog
   */
  public static function blogAuteurSecondaire()
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    if ($node instanceof \Drupal\node\NodeInterface) {
      // You can get nid and anything else you need from the node object.
      $authors = $node->get('field_auteur_secondaire')->getValue();
      if(!empty($authors)){
        foreach($authors as $author){
          $author_id = isset($author['target_id']) ? $author['target_id'] : '';
          $entity_type = 'user';
          $view_mode = 'blog_liste_bas';
          if(!empty($author_id)) {
            $user = \Drupal::entityTypeManager()->getStorage($entity_type)->load($author_id);
            if(!empty($user)){
              $render = \Drupal::entityTypeManager()->getViewBuilder($entity_type)->view($user, $view_mode);
              $output = render($render);
              print $output;
            }
          }
        }
      }
    }
  }
}
