<?php
/**
 * Social feeds plugin for Craft CMS 3.x
 *
 * Utilizes json api to get latest social feeds
 *
 * @link      https://apt.no
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\socialfeeds\services;

use apt\socialfeeds\SocialFeeds;

use Craft;
use craft\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

interface iSocialService
{
    public function getActivated();
    public function isActivated();
    public function getFeed($limit);
}

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
abstract class SocialService extends Component implements iSocialService
{
    static protected $cacheKey = 'apt_social_feed';

    protected $settings;

    protected $activated = false;

    // Public Methods
    // =========================================================================

    public function __construct()
    {
        parent::__construct();

        $this->settings = SocialFeeds::$plugin->getSettings();
    }

    public function getActivated() : bool
    {
        return $this->activated;
    }

    public function isActivated() : bool
    {
        return $this->getActivated();
    }
}
