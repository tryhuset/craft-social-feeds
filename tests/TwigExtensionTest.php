<?php

use apt\socialfeeds\twigextensions\TwigExtension;

class TwigExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function json_prettify()
    {
        $json = json_encode(['foo' => 'bar']);
        $extension = new TwigExtension;
        $this->assertStringStartsWith(
            "{\r\n",
            $extension->jsonPrettify($json)
        );
    }

    /**
     * @test
     */
    public function emoji_shortcode()
    {
        $extension = new TwigExtension;
        $this->assertEquals(
            ":grinning:",
            $extension->emojiShortcode('😀')
        );
    }

    /**
     * @test
     */
    public function emoji_html()
    {
        $extension = new TwigExtension;
        $this->assertEquals(
            "foo &#x1F600; bar",
            $extension->emojiHTML('foo :grinning: bar')
        );
    }

    /**
     * @test
     */
    public function emoji_unicode()
    {
        $extension = new TwigExtension;
        $this->assertEquals(
            "foo 😀 bar",
            $extension->emojiUnicode('foo :grinning: bar')
        );
    }
}
