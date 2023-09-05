<?php

namespace Drupal\twig_filters\TwigExtension;


/**
 * Class FormatFileSizeExtension.
 */
class FormatFileSizeExtension extends \Twig_Extension {
   /**
    * {@inheritdoc}
    */
    public function getFilters() {
      return [ new \Twig_SimpleFilter('format_file_size', array($this, 'formatFileSize'))];
    }

   /**
    * {@inheritdoc}
    */
    public function getName() {
      return 'twig_filters.format_file_size';
    }

    public static function formatFileSize($string) {
      return format_size($string);
    }

}
