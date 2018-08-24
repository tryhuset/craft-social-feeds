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
use GuzzleHttp\Exception\ClientException;
use yii\caching\ExpressionDependency;
use apt\socialfeeds\SocialFeeds;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Instagram extends SocialService
{
    static protected $cacheKey = 'apt_social_feed_instagram';

    protected $activated;

    protected $id;

    protected $token;

    // Public Methods
    // =========================================================================
    public function __construct()
    {
        parent::__construct();

        $this->activated = ($this->settings->instagram && $this->settings->instagramOn);
        $this->id = $this->settings->instagramId;
        $this->token = $this->settings->instagramToken;
    }

    /*
     * @return mixed
     */
    public function getFeed($limit = 6) : array
    {
        if (!$this->activated) {
            return [
                'status' => 403,
                'message' => 'Instagram is not activated',
            ];
        }

        $cacheKey = [self::$cacheKey, $limit];

        /* get cached version if exists */
        $items = Craft::$app->cache->get($cacheKey);

        if (empty($items)) {
            $items = [];
            try {
                $client = new Client([
                    'base_uri' => 'https://api.instagram.com/v1/',
                ]);

                $res = $client->get("users/{$this->id}/media/recent", ['query' => [
                    'access_token' => $this->token,
                    'count' => $limit,
                ]]);
                $data = json_decode($res->getBody(), true);
                foreach ($data['data'] as $item) {
                    $items[] = [
                        'id' => $item['id'],
                        'time' => date('c', $item['created_time']),
                        'title' => $item['caption']['text'],
                        'link' => $item['link'],
                        'image' => $item['images']['low_resolution']['url'],
                    ];
                }
                $dependency = new ExpressionDependency([
                    'expression' => 'apt\\socialfeeds\\SocialFeeds::$plugin->getSettings()->getInstagramStateString() == $this->params["state"]',
                    'params' => [
                        'state' => $this->settings->getInstagramStateString(),
                    ],
                ]);
                Craft::$app->cache->set($cacheKey, $items, 600);
            } catch (ClientException $e) {
                $res = $e->getResponse();
                $data = json_decode($res->getBody(), JSON_UNESCAPED_UNICODE);
                if (array_key_exists('meta', $data)) {
                    return [
                        'error' => true,
                        'status' => $res->getStatusCode(),
                        'code' => $data['meta']['code'],
                        'type' => $data['meta']['error_type'],
                        'message' => $data['meta']['error_message'],
                    ];
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


        return $items;
    }
}
