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
use LitEmoji\LitEmoji;
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
            new \Twig_SimpleFilter('emoji_shortcode', [$this, 'emojiShortcode']),
            new \Twig_SimpleFilter('emoji_html', [$this, 'emojiHTML']),
            new \Twig_SimpleFilter('emoji_unicode', [$this, 'emojiUnicode']),
        ];
    }

    public function getFunctions()
    {
        return [];
    }

    public function jsonPrettify($json)
    {
        return $this->prettifier->prettify($json);
    }

    /**
     * @param null $text
     *
     * @return string
     */
    public function emojiShortcode($text)
    {
        return LitEmoji::encodeShortcode($text);
    }

    /**
     * @param null $text
     *
     * @return string
     */
    public function emojiHTML($text)
    {
        return LitEmoji::encodeHtml($text);
    }

    /**
     * @param null $text
     *
     * @return string
     */
    public function emojiUnicode($text)
    {
        return LitEmoji::encodeUnicode($text);
    }
}
