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
use LitEmoji\LitEmoji;

interface iSocialService
{
    public function getActivated();
    public function isActivated();
    public function getFeedWithErrors($limit);
    public function executeLookup($limit);
}

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
abstract class SocialService extends Component implements iSocialService
{
    static protected $cacheKey = 'apt_social_feed';

    protected $activated = false;

    protected $cache;

    protected $clientClass;

    protected $state = '';

    public function __construct($config = [])
    {
        parent::__construct();

        $config = array_merge([
            'clientClass' => Client::class,
            'cache' => Craft::$app->cache,
        ], $config);

        foreach ($config as $key => $value) {
            if (property_exists($this , $key)) {
                $this->$key = $value;
            }
        }
    }

    protected function getClient($config = [])
    {
        return new $this->clientClass($config);
    }

    public function getActivated() : bool
    {
        return $this->activated;
    }

    public function isActivated() : bool
    {
        return $this->getActivated();
    }

    public function encodeEmojis($string) : string
    {
        return LitEmoji::encodeShortcode($string);
    }

    public function getFeedWithoutErrors($limit)
    {
        try {
            return $this->executeLookup($limit);
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getFeed($limit = 6, $errors = false)
    {
        if ($errors) {
            return $this->getFeedWithErrors($limit);
        }
        return $this->getFeedWithoutErrors($limit);
    }
}
