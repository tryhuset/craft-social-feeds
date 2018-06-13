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
class Youtube extends Component
{
    static protected $cacheKey = 'apt_social_feed_youtube';

    protected $activated;

    protected $channel;

    protected $key;

    // Public Methods
    // =========================================================================
    public function __construct()
    {
        parent::__construct();

        $settings = SocialFeeds::$plugin->getSettings();

        $this->activated = $settings->youtubeOn;
        $this->channel = $settings->youtubeChannel;
        $this->key = $settings->youtubeKey;
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
                    'base_uri' => 'https://www.googleapis.com/youtube/v3/',
                ]);

                $res = $client->get('channels', ['query' => [
                    'part' => 'contentDetails',
                    'id' => $this->channel,
                    'key' => $this->key,
                ]]);
                $data = json_decode($res->getBody(), true);

                $channel = array_shift($data['items']);
                if ($channel) {
                    $playlistId = $channel['contentDetails']['relatedPlaylists']['uploads'];

                    $res = $client->get('playlistItems', ['query' => [
                        'part' => 'snippet',
                        'maxResults' => $limit,
                        'playlistId' => $playlistId,
                        'key' => $this->key,
                    ]]);

                    $data = json_decode($res->getBody(), true);

                    foreach ($data['items'] as $item) {
                        $id = $item['snippet']['resourceId']['videoId'];
                        $items[] = [
                            'id' => $id,
                            'time' => $item['snippet']['publishedAt'],
                            'title' => $item['snippet']['title'],
                            'link' => "https://www.youtube.com/watch?v=$id",
                            'image' => $item['snippet']['thumbnails']['high']['url'],
                        ];
                    }
                }
                Craft::$app->cache->set($cacheKey, $items, 600);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        return $items;
    }
}
