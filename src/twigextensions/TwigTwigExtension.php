<?php
/**
 * Twig plugin for Craft CMS 3.x
 *
 * Extend Twig
 *
 * @link      https://apt.no
 * @copyright Copyright (c) 2018 apt
 */

namespace apt\socialfeeds\twigextensions;

use apt\twig\Twig;
use Camspiers\JsonPretty\JsonPretty;

use Craft;

/**
 * Twig can be extended in many ways; you can add extra tags, filters, tests, operators,
 * global variables, and functions. You can even extend the parser itself with
 * node visitors.
 *
 * http://twig.sensiolabs.org/doc/advanced.html
 *
 * @author    apt
 * @package   Twig
 * @since     1.0.0
 */
class TwigTwigExtension extends \Twig_Extension
{
    public function __construct()
    {
        $this->prettifier = new JsonPretty();
    }

    public function getName()
    {
        return 'Twig';
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('json_prettify', [$this, 'jsonPrettify']),
        ];
    }

    public function getFunctions()
    {
        return [
        ];
    }

    public function toFloat($value)
    {
        $val = filter_var($value, FILTER_VALIDATE_FLOAT);
        if($val){
            return $val;
        }
        return $value;
    }

    public function decimalCount($string)
    {
      if (empty($string)) {
        return 0;
      }
      $count = strlen(substr(strrchr($string, "."), 1));
      return intval($count);
    }

    public function nl2p($str)
    {
        $arr=explode("\n",$str);
        $out='';

        for($i=0;$i<count($arr);$i++) {
            if(strlen(trim($arr[$i]))>0)
                $out.='<p>'.trim($arr[$i]).'</p>';
        }
        return $out;
    }

    public function abbreviateWords($string, $charcount)
    {
        if (strlen($string) <= $charcount) {
            return trim($string);
        }
        $strings = explode(' ', $string);
        $result = '';
        if (empty($strings) == false) {
            foreach ($strings as $word) {
                if (strlen("$result $word") > $charcount) {
                    break;
                }
                $result .= " $word";
            }
            if (strlen($string) > strlen($result)) {
                $result .= " …";
            }
            return trim($result);
        }
        return trim($string);
    }

    public function jsonPrettify($json)
    {
        return $this->prettifier->prettify($json);
    }
}
