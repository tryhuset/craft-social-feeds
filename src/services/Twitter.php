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


/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Twitter extends Component
{
    static protected $cacheKey = 'apt_social_feed_twitter';

    protected $activated;
    protected $consumerKey;
    protected $consumerSecret;
    protected $token;
    protected $tokenSecret;
    protected $screenName;

    // Public Methods
    // =========================================================================
    public function __construct()
    {
        parent::__construct();

        $settings = SocialFeeds::$plugin->getSettings();

        $this->activated = $settings->twitterOn;
        $this->consumerKey = $settings->twitterConsumerKey;
        $this->consumerSecret = $settings->twitterConsumerSecret;
        $this->token = $settings->twitterToken;
        $this->tokenSecret = $settings->twitterTokenSecret;
        $this->screenName = $settings->twitterScreenName;
    }

    public function getActivated() : bool
    {
        return $this->activated;
    }

    /*
     * @return mixed
     */
    public function getFeed($limit = 6)
    {
        $cacheKey = self::$cacheKey."_{$limit}";

        /* get cached version if exists */
        $items = Craft::$app->cache->get($cacheKey);

        if (empty($tweets)) {
            $items = [];
            try {
                $stack = HandlerStack::create();
                $middleware = new Oauth1([
                    'consumer_key'    => $this->consumerKey,
                    'consumer_secret' => $this->consumerSecret,
                    'token'           => $this->token,
                    'token_secret'    => $this->tokenSecret,
                ]);

                $stack->push($middleware);

                $client = new Client([
                    'base_uri' => 'https://api.twitter.com/1.1/',
                    'handler' => $stack,
                    'auth' => 'oauth',
                ]);

                $res = $client->get('statuses/user_timeline.json',['query' => [
                    'screen_name' => $this->screenName,
                    'count' => $limit,
                ]]);

                $data = json_decode($res->getBody(), true);
                foreach ($data as $tweet) {
                    $items[] = [
                        'id' => $tweet['id_str'],
                        'text' => $tweet['text'],
                        'time' => $tweet['created_at'],
                        'user' => $tweet['user']['screen_name'],
                    ];
                }
                Craft::$app->cache->set($cacheKey, $items, 600);
            } catch (\Exception $e) {}
        }

        return $items;
    }
}
