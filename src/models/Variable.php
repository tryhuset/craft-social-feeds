<?php
/**
 * Social feeds plugin for Craft CMS 3.x
 *
 * Utilizes json api to get latest social feeds
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\socialfeeds\models;

use Craft;
use craft\base\Model;
use apt\socialfeeds\SocialFeeds;
use apt\socialfeeds\services;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Variable extends Model
{
    protected $services;

    public function __construct()
    {
        $this->services = SocialFeeds::getInstance();
    }

    public function getActivated($key) : bool
    {
        switch ($key) {
            case 'facebook':
                return $this->services->facebook->getActivated();
            case 'youtube':
                return $this->services->youtube->getActivated();
            case 'twitter':
                return $this->services->twitter->getActivated();
            case 'instagram':
                return $this->services->instagram->getActivated();
            case 'flickr':
                return $this->services->flickr->getActivated();
            default:
                return false;
        }
    }

    public function getFeed($key, $limit = 6) : array
    {
        switch ($key) {
            case 'facebook':
                return $this->services->facebook->getFeed($limit);
            case 'youtube':
                return $this->services->youtube->getFeed($limit);
            case 'twitter':
                return $this->services->twitter->getFeed($limit);
            case 'instagram':
                return $this->services->instagram->getFeed($limit);
            case 'flickr':
                return $this->services->flickr->getFeed($limit);
            default:
                return [
                    'status' => 404,
                    'message' => "Social service \"$key\" doesen't exist",
                ];
        }
    }
}
