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

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Instagram extends Component
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

        $settings = SocialFeeds::$plugin->getSettings();

        $this->activated = $settings->instagramOn;
        $this->id = $settings->instagramId;
        $this->token = $settings->instagramToken;
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
                Craft::$app->cache->set($cacheKey, $items, 600);
            } catch (\Exception $e) {}
        }


        return $items;
    }
}
