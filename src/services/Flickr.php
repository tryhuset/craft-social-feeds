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

    public function executeLookup($limit = 6) : array
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
                        'title' => $this->encodeEmojis($item['title']),
                        'link' => $item['link'],
                        'image' => $item['media']['m'],
                        'tags' => explode(' ', $item['tags']),
                    ];
                }
            }
            $dependency = new ExpressionDependency([
                'expression' => 'apt\\socialfeeds\\SocialFeeds::$plugin->getSettings()->getFlickrStateString() == $this->params["state"]',
                'params' => [
                    'state' => $this->settings->getFlickrStateString(),
                ],
            ]);
            Craft::$app->cache->set(self::$cacheKey, $items, 600, $dependency);
        }

        return array_splice($items, 0, $limit);
    }

    public function getFeedWithErrors($limit = 6)
    {
        try {
            return $this->executeLookup($limit);
        } catch (ClientException $e) {
            $res = $e->getResponse();
            if ($res->getStatusCode() === 404) {
                return [
                    'error' => true,
                    'status' => 404,
                    'message' => Craft::t('apt-social-feeds', 'Ficker id {id} not found', ['id' => $this->id]),
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
}
