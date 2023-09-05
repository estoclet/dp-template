<?php

namespace Drupal\twig_filters\TwigExtension;


/**
 * Class GetColorClassExtension.
 */
class GetColorClassExtension extends \Twig_Extension {
   /**
    * {@inheritdoc}
    */
    public function getFunctions() {
      return [
        new \Twig_SimpleFunction('getColorClass', array($this, 'getColorClass')),
        new \Twig_SimpleFunction('getBgColorClass', array($this, 'getBgColorClass')),
      ];
    }


   /**
    * {@inheritdoc}
    */
    public function getName() {
      return 'twig_functions.get_color_class';
    }

  public static function getColorClass($color) {
    switch ($color) {
      case '#E30613':
        return 'CFrouge';
      case '#FFFFFF':
        return 'CFblanc';
      case '#D7C3BE':
        return 'CFbeige';
      case '#A0B4D2':
        return 'CFbleuclair';
      case '#B4AAC8':
        return 'CFvioletclair';
      case '#AACDC8':
        return 'CFvertclair';
      case '#820A41':
        return 'CFmauve';
      case '#003264':
        return 'CFbleufonce';
      case '#550055':
        return 'CFvioletfonce';
      case '#006464':
        return '.CFvertfonce';
      case '#F39869':
        return 'CCorangeclair';
      case '#E67BBC':
        return 'CCroseclair';
      case '#AA5AA5':
        return 'CCmauve';
      case '#5F5FAA':
        return 'CClavande';
      case '#82D2FA':
        return 'CCbleuazur';
      case '#73CBAA':
        return 'CCvertclair';
      case '#E73446':
        return 'CCorange';
      case '#E600B7':
        return 'CCrose';
      case '#8C2896':
        return 'CCviolet';
      case '#1E46A0':
        return 'CCbleufonce';
      case '#00AAFA':
        return 'CCbleuclair';
      case '#009A93':
        return 'CCvertfonce';
      default:
        return $color;
    }
  }

  public static function getBgColorClass($color) {
    switch ($color) {
      case '#E30613':
        return 'CFrougeBg';
      case '#FFFFFF':
        return 'CFblancBg';
      case '#D7C3BE':
        return 'CFbeigeBg';
      case '#A0B4D2':
        return 'CFbleuclairBg';
      case '#B4AAC8':
        return 'CFvioletclairBg';
      case '#AACDC8':
        return 'CFvertclairBg';
      case '#820A41':
        return 'CFmauveBg';
      case '#003264':
        return 'CFbleufonceBg';
      case '#550055':
        return 'CFvioletfonceBg';
      case '#006464':
        return 'CFvertfonceBg';
      case '#F39869':
        return 'CCorangeclairBg';
      case '#E67BBC':
        return 'CCroseclairBg';
      case '#AA5AA5':
        return 'CCmauveBg';
      case '#5F5FAA':
        return 'CClavandeBg';
      case '#82D2FA':
        return 'CCbleuazurBg';
      case '#73CBAA':
        return 'CCvertclairBg';
      case '#E73446':
        return 'CCorangeBg';
      case '#E600B7':
        return 'CCroseBg';
      case '#8C2896':
        return 'CCvioletBg';
      case '#1E46A0':
        return 'CCbleufonceBg';
      case '#00AAFA':
        return 'CCbleuclairBg';
      case '#009A93':
        return 'CCvertfonceBg';
      default:
        return $color;
    }
  }

}
