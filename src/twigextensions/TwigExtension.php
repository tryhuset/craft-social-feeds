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
class TwigExtension extends \Twig\Extension\AbstractExtension
{
    public function getName()
    {
        return 'Twig';
    }

    public function getFilters()
    {
        return [
            new \Twig\TwigFilter('json_prettify', [$this, 'jsonPrettify']),
            new \Twig\TwigFilter('emoji_shortcode', [$this, 'emojiShortcode']),
            new \Twig\TwigFilter('emoji_html', [$this, 'emojiHTML']),
            new \Twig\TwigFilter('emoji_unicode', [$this, 'emojiUnicode']),
        ];
    }

    public function getFunctions()
    {
        return [];
    }

    public function jsonPrettify($json)
    {
        if (is_string($json)) {
            $decoded = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }
        }
        return json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
