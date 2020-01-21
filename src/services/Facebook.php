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
use GuzzleHttp\Exception\ClientException;
use yii\caching\ExpressionDependency;
use apt\socialfeeds\SocialFeeds;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Facebook extends SocialService
{

    static protected $cacheKey = 'apt_social_feed_facebook';

    protected $appId;

    protected $appSecret;

    protected $pageId;

    protected $accessToken;

    /*
     * @return mixed
     */
    public function executeLookup($limit = 6)
    {
        if (!$this->activated) {
            return [
                'status' => 403,
                'message' => Craft::t('apt-social-feeds', 'Facebook is not activated'),
            ];
        }

        /* $query = [
            'access_token' => "{$this->appId}|{$this->appSecret}",
            'fields' => 'story,message,attachments,link,created_time',
        ]; */

        $query = [
            'access_token' => "{$this->accessToken}",
            'fields' => 'message,attachments,created_time',
        ];

        if ($limit) {
            $query['limit'] = $limit;
        }

        $cacheKey = [
            self::$cacheKey,
            $limit,
        ];

        /* get cached version if exists */
        $items = $this->cache->get($cacheKey);

        if (empty($items)) {
            $items = [];
            $client = $this->getClient([
                'base_uri' => 'https://graph.facebook.com/',
            ]);
            $res = $client->get("{$this->pageId}/feed", ['query' => $query]);
            $data = json_decode($res->getBody(), JSON_UNESCAPED_UNICODE);

            foreach ($data['data'] as $item) {
                $time = new \DateTime($item['created_time']);
                if (isset($item['attachments'])) {
                    $item['image'] = $this->getFacebookImage($item['attachments']);
                    unset($item['attachments']);
                }

                unset($item['created_time']);
                $item['time'] = $time->format('c');
                if (isset($item['message'])) {
                    $item['message'] = $this->encodeEmojis($item['message']);
                }

                $items[] = $item;
            }

            $dependency = new ExpressionDependency([
                'expression' => 'apt\\socialfeeds\\SocialFeeds::$plugin->getSettings()->getFacebookStateString() == $this->params["state"]',
                'params' => [
                    'state' => $this->state,
                ],
            ]);
            $this->cache->set($cacheKey, $items, 600, $dependency);

            return $items;
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

    protected function getFacebookImage($attachments)
    {
        if (!isset($attachments['data'])) {
            return null;
        }
        $data = array_shift($attachments['data']);
        if (isset($data['media']['image'])) {
            return $data['media']['image'];
        }
        if (isset($data['subattachments']['data'])) {
            $firstMedia = array_shift($data['subattachments']['data']);
            return $firstMedia['media']['image'];
        }
        return $attachments;
    }
}
