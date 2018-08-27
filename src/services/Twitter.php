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

use Craft;
use craft\base\Component;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\ClientException;
use yii\caching\ExpressionDependency;
use apt\socialfeeds\SocialFeeds;


/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Twitter extends SocialService
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

        $this->activated = ($this->settings->twitter && $this->settings->twitterOn);
        $this->consumerKey = $this->settings->twitterConsumerKey;
        $this->consumerSecret = $this->settings->twitterConsumerSecret;
        $this->token = $this->settings->twitterToken;
        $this->tokenSecret = $this->settings->twitterTokenSecret;
        $this->screenName = $this->settings->twitterScreenName;
    }

    /*
     * @return mixed
     */
    public function executeLookup($limit = 6) : array
    {
        if (!$this->activated) {
            return [
                'status' => 403,
                'message' => 'Twitter is not activated',
            ];
        }

        $cacheKey = [self::$cacheKey, $limit];

        /* get cached version if exists */
        $items = Craft::$app->cache->get($cacheKey);

        if (empty($items)) {
            $items = [];
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
                    'text' => $this->encodeEmojis($tweet['text']),
                    'time' => $tweet['created_at'],
                    'user' => $tweet['user']['screen_name'],
                ];
            }

            $dependency = new ExpressionDependency([
                'expression' => 'apt\\socialfeeds\\SocialFeeds::$plugin->getSettings()->getTwitterStateString() == $this->params["state"]',
                'params' => [
                    'state' => $this->settings->getTwitterStateString(),
                ],
            ]);
            Craft::$app->cache->set($cacheKey, $items, 600, $dependency);
        }

        return $items;
    }

    public function getFeedWithErrors($limit = 6)
    {
        try {
            return $this->executeLookup($limit);
        } catch (ClientException $e) {
            $res = $e->getResponse();
            $data = json_decode($res->getBody(), JSON_UNESCAPED_UNICODE);
            if (array_key_exists('errors', $data)) {
                if (is_array($data['errors'])) {
                    $error = array_shift($data['errors']);
                    return array_merge([
                        'error' => true,
                        'status' => $res->getStatusCode(),
                    ], $error);
                }
                return array_merge([
                    'error' => true,
                    'status' => $res->getStatusCode(),
                ], $data['errors']);
            }
            return [
                'error' => true,
                'status' => $e->getCode(),
                'message' => Craft::t('apt-social-feeds', 'An error occured'),
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'status' => 500,
                'message' => Craft::t('apt-social-feeds', 'An error occured'),
            ];
        }
    }
}
