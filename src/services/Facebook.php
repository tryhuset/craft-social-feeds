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
use GuzzleHttp\Client;
use apt\socialfeeds\SocialFeeds;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Facebook extends SocialService
{

    static protected $cacheKey = 'apt_social_feed_facebook';

    protected $activated;

    protected $accessToken;

    protected $pageId;

    // Public Methods
    // =========================================================================
    public function __construct()
    {
        parent::__construct();

        $this->activated = ($this->settings->facebook && $this->settings->facebookOn);
        $this->accessToken = $this->settings->facebookAccessToken;
        $this->pageId = $this->settings->facebookPageId;
    }

    /*
     * @return mixed
     */
    public function getFeed($limit = 6)
    {
        if (!$this->activated) {
            return [
                'status' => 403,
                'message' => 'Facebook is not activated',
            ];
        }

        $query = [
            'access_token' => $this->accessToken,
            'fields' => 'story,message,attachments,link,created_time',
        ];

        if ($limit) {
            $query['limit'] = $limit;
        }
        $cacheKey = self::$cacheKey."_{$limit}";

        /* get cached version if exists */
        $items = Craft::$app->cache->get($cacheKey);

        if (empty($items)) {
            $items = [];
            try {
                $client = new Client([
                    'base_uri' => 'https://graph.facebook.com/',
                ]);

                $res = $client->get("{$this->pageId}/posts", ['query' => $query]);
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
                        $item['message'] = $this->manageEmoji($item['message']);
                    }

                    $items[] = $item;
                }
                Craft::$app->cache->set($cacheKey, $items, 600);
            } catch (\Exception $e) {}
        }

        return $items;
    }

    protected function manageEmoji($text)
    {
        $cleanText = "";

        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $cleanText = preg_replace($regexEmoticons, '', $text);

        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $cleanText = preg_replace($regexSymbols, '', $cleanText);

        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $cleanText = preg_replace($regexTransport, '', $cleanText);

        return $cleanText;
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
