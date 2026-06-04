<?php

use apt\socialfeeds\twigextensions\TwigExtension;
use PHPUnit\Framework\Attributes\Test;

class TwigExtensionTest extends \PHPUnit\Framework\TestCase
{
    #[Test]
    public function json_prettify()
    {
        $json = json_encode(['foo' => 'bar']);
        $extension = new TwigExtension;
        $this->assertStringStartsWith(
            "{\n",
            $extension->jsonPrettify($json)
        );
    }

    #[Test]
    public function emoji_shortcode()
    {
        $extension = new TwigExtension;
        $this->assertEquals(
            ":grinning_face:",
            $extension->emojiShortcode('😀')
        );
    }

    #[Test]
    public function emoji_html()
    {
        $extension = new TwigExtension;
        $this->assertEquals(
            "foo &#x1F600; bar",
            $extension->emojiHTML('foo :grinning: bar')
        );
    }

    #[Test]
    public function emoji_unicode()
    {
        $extension = new TwigExtension;
        $this->assertEquals(
            "foo 😀 bar",
            $extension->emojiUnicode('foo :grinning: bar')
        );
    }
}
