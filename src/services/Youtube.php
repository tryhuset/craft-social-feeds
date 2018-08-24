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
class Youtube extends SocialService
{
    static protected $cacheKey = 'apt_social_feed_youtube';

    protected $activated;

    protected $type;

    protected $playlist;

    protected $channel;

    protected $key;

    // Public Methods
    // =========================================================================
    public function __construct()
    {
        parent::__construct();

        $this->activated = ($this->settings->youtube && $this->settings->youtubeOn);
        $this->type = $this->settings->youtubeType;
        $this->playlist = $this->settings->youtubePlaylist;
        $this->channel = $this->settings->youtubeChannel;
        $this->key = $this->settings->youtubeKey;
    }

    protected function getPlaylistId() : string
    {
        if ($this->type === 'playlist') {
            return $this->playlist;
        }

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
            return $channel['contentDetails']['relatedPlaylists']['uploads'];
        }

        return '';
    }

    /*
     * @return mixed
     */
    public function getFeed($limit = 6)
    {
        if (!$this->activated) {
            return [
                'error' => true,
                'status' => 403,
                'message' => 'Youtube is not activated',
            ];
        }

        $cacheKey = [
            self::$cacheKey,
            $this->type,
            $limit,
        ];
        /* get cached version if exists */
        $items = Craft::$app->cache->get($cacheKey);

        if (empty($items)) {
            $items = [];
            try {
                $client = new Client([
                    'base_uri' => 'https://www.googleapis.com/youtube/v3/',
                ]);

                $playlistId = $this->getPlaylistId();

                if ($playlistId) {
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

                $dependency = new ExpressionDependency([
                    'expression' => 'apt\\socialfeeds\\SocialFeeds::$plugin->getSettings()->getYoutubeStateString() == $this->params["state"]',
                    'params' => [
                        'state' => $this->settings->getYoutubeStateString(),
                    ],
                ]);
                Craft::$app->cache->set($cacheKey, $items, 600, $dependency);
            } catch (ClientException $e) {
                $res = $e->getResponse();
                $data = json_decode($res->getBody(), JSON_UNESCAPED_UNICODE);
                if (array_key_exists('error', $data)) {
                    return array_merge([
                        'error' => true,
                        'status' => $res->getStatusCode(),
                    ], $data['error']);
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
