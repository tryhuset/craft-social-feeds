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

use apt\socialfeeds\SocialFeeds;

use Craft;
use craft\base\Model;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $facebook = true;
    public $youtube = true;
    public $twitter = true;
    public $instagram = true;
    public $flickr = true;

    /**
     * @var string
     */
    public $twitterOn = false;
    public $twitterConsumerKey = '';
    public $twitterConsumerSecret = '';
    public $twitterToken = '';
    public $twitterTokenSecret = '';
    public $twitterScreenName = '';


    public $flickrOn = false;
    public $flickrId = '';

    public $instagramOn = false;
    public $instagramId = '';
    public $instagramToken = '';

    public $youtubeOn = false;
    public $youtubeType = 'playlist';
    public $youtubeChannel = '';
    public $youtubePlaylist = '';
    public $youtubeKey = '';

    public $facebookOn = false;
    public $facebookAppId = '';
    public $facebookAppSecret = '';
    public $facebookAccessToken = '';
    public $facebookPageId = '';


    public function __construct($config = null)
    {
        unset($config['facebookAccessToken']);
        parent::__construct($config);
    }

    protected function getTwitterRules()
    {
        if (!$this->twitter) {
            return [];
        }
        $rules = [
            ['twitterOn', 'boolean'],
            ['twitterOn', 'default', 'value' => false],
            [['twitterConsumerKey', 'twitterConsumerSecret', 'twitterToken', 'twitterTokenSecret', 'twitterScreenName'], 'string'],

            [['twitterConsumerKey', 'twitterConsumerSecret', 'twitterToken', 'twitterTokenSecret', 'twitterScreenName'], 'default', 'value' => ''],
        ];

        if ($this->twitterOn) {
            $rules = array_merge($rules, [
                [['twitterConsumerKey', 'twitterConsumerSecret', 'twitterToken', 'twitterTokenSecret', 'twitterScreenName'], 'required'],
            ]);
        }

        return $rules;
    }

    protected function getflickrRules()
    {
        if (!$this->flickr) {
            return [];
        }
        $rules = [
            ['flickrOn', 'boolean'],
            ['flickrOn', 'default', 'value' => false],
            ['flickrId', 'string'],
            ['flickrId', 'default', 'value' => ''],
        ];

        if ($this->flickrOn) {
            $rules = array_merge($rules, [
                ['flickrId', 'required'],
            ]);
        }

        return $rules;
    }

    protected function getInstagramRules()
    {
        if (!$this->instagram) {
            return [];
        }
        $rules = [
            ['instagramOn', 'boolean'],
            ['instagramOn', 'default', 'value' => false],
            ['instagramId', 'string'],
            ['instagramId', 'default', 'value' => ''],
            ['instagramToken', 'string'],
            ['instagramToken', 'default', 'value' => ''],
        ];

        if ($this->instagramOn) {
            $rules = array_merge($rules, [
                ['instagramId', 'required'],
                ['instagramToken', 'required'],
            ]);
        }

        return $rules;
    }

    protected function getYoutubeRules()
    {
        if (!$this->youtube) {
            return [];
        }

        $rules = [
            ['youtubeOn', 'boolean'],
            ['youtubeOn', 'default', 'value' => false],
            ['youtubeType', 'string'],
            ['youtubeType', 'default', 'value' => 'playlist'],
            ['youtubePlaylist', 'string'],
            ['youtubePlaylist', 'default', 'value' => ''],
            ['youtubeChannel', 'string'],
            ['youtubeChannel', 'default', 'value' => ''],
            ['youtubeKey', 'string'],
            ['youtubeKey', 'default', 'value' => ''],
        ];

        if ($this->youtubeOn) {
            $rules = array_merge($rules, [
                ['youtubeType', 'required'],
                ['youtubeKey', 'required'],
            ]);

            if ($this->youtubeType === 'playlist') {
                $rules = array_merge($rules, [
                    ['youtubePlaylist', 'required'],
                ]);
            } else {
                $rules = array_merge($rules, [
                    ['youtubeChannel', 'required'],
                ]);
            }
        }

        return $rules;
    }

    protected function getFacebookRules()
    {
        if (!$this->facebook) {
            return [];
        }
        $rules = [
            ['facebookOn', 'boolean'],
            ['facebookOn', 'default', 'value' => false],
            ['facebookAppId', 'string'],
            ['facebookAppId', 'default', 'value' => ''],
            ['facebookAppSecret', 'string'],
            ['facebookAppSecret', 'default', 'value' => ''],
            ['facebookPageId', 'string'],
            ['facebookPageId', 'default', 'value' => ''],
            ['facebookAccessToken', 'string'],
            ['facebookAccessToken', 'default', 'value' => ''],
        ];

        if ($this->facebookOn) {
            $rules = array_merge($rules, [
                //['facebookAppId', 'required'],
                //['facebookAppSecret', 'required'],
                ['facebookPageId', 'required'],
                ['facebookAccessToken', 'required'],
            ]);
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $global = [
            [['facebook', 'youtube', 'instagram', 'flickr'], 'boolean'],
            [['facebook', 'youtube', 'instagram', 'flickr'], 'default', 'value' => false],
        ];
        $twitter = $this->getTwitterRules();
        $flickr = $this->getflickrRules();
        $instagram = $this->getInstagramRules();
        $youtube = $this->getYoutubeRules();
        $facebook = $this->getFacebookRules();

        return array_merge($global, $twitter, $flickr, $instagram, $youtube, $facebook);
    }

    public function getTwitterStateString()
    {
        return "{$this->twitterConsumerKey}_{$this->twitterConsumerSecret}_{$this->twitterToken}_{$this->twitterTokenSecret}_{$this->twitterScreenName}";
    }

    public function getYoutubeStateString()
    {
        return "{$this->youtubeType}_{$this->youtubePlaylist}_{$this->youtubeChannel}_{$this->youtubeKey}";
    }

    public function getInstagramStateString()
    {
        return "{$this->instagramId}_{$this->instagramToken}";
    }

    public function getFlickrStateString()
    {
        return "{$this->flickrId}";
    }

    public function getFacebookStateString()
    {
        return "{$this->facebookAccessToken}_{$this->facebookPageId}";
        //return "{$this->facebookAppId}_{$this->facebookAppSecret}_{$this->facebookPageId}";
    }
}
