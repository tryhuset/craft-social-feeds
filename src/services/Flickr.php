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
use apt\socialfeeds\SocialFeeds;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Flickr extends SocialService
{
    static protected $cacheKey = 'apt_social_feed_flickr';

    protected $activated;

    protected $id;

    // Public Methods
    // =========================================================================

    public function __construct()
    {
        parent::__construct();

        $this->activated = ($this->settings->flickr && $this->settings->flickrOn);
        $this->id = $this->settings->flickrId;
    }

    public function getFeed($limit = 6) : array
    {
        if (!$this->activated) {
            return [
                'status' => 403,
                'message' => 'Flickr is not activated',
            ];
        }

        /* get cached version if exists */
        $items = Craft::$app->cache->get(self::$cacheKey);

        if (empty($items)) {

            $items = [];
            try {
                $client = new Client([
                    'base_uri' => 'https://api.flickr.com/services/feeds/',
                ]);

                $res = $client->get('photos_public.gne', ['query' => [
                    'id' => $this->id,
                    'format' => 'json',
                    'nojsoncallback' => 1,
                ]]);
                $data = json_decode($res->getBody(), true);
                foreach ($data['items'] as $item) {
                    if (preg_match("/.*\/([^]]+)\//", $item['link'], $matches )) {
                        $items[] = [
                            'id' => $matches[1],
                            'time' => $item['published'],
                            'title' => $item['title'],
                            'link' => $item['link'],
                            'image' => $item['media']['m'],
                        ];
                    }
                }
                Craft::$app->cache->set(self::$cacheKey, $items, 600);
            } catch (\Exception $e) {}
        }

        return array_splice($items, 0, $limit);
    }
}
