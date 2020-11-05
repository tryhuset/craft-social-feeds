<?php
/**
 * Social feeds plugin for Craft CMS 3.x
 *
 * Utilizes json api to get latest social feeds
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\socialfeeds\assetbundles\socialfeeds;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class SocialFeedsAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@apt/socialfeeds/assetbundles/socialfeeds/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/SocialFeeds.js',
        ];

        $this->css = [
            'css/SocialFeeds.css',
        ];

        parent::init();
    }
}
