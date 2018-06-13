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
    public $youtubeChannel = '';
    public $youtubeKey = '';

    public $facebookOn = false;
    public $facebookAccessToken = '';
    public $facebookPageId = '';

    // Public Methods
    // =========================================================================

    protected function getTwitterRules()
    {
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
        $rules = [
            ['youtubeOn', 'boolean'],
            ['youtubeOn', 'default', 'value' => false],
            ['youtubeChannel', 'string'],
            ['youtubeChannel', 'default', 'value' => ''],
            ['youtubeKey', 'string'],
            ['youtubeKey', 'default', 'value' => ''],
        ];

        if ($this->youtubeOn) {
            $rules = array_merge($rules, [
                ['youtubeChannel', 'required'],
                ['youtubeKey', 'required'],
            ]);
        }

        return $rules;
    }

    protected function getFacebookRules()
    {
        $rules = [
            ['facebookOn', 'boolean'],
            ['facebookOn', 'default', 'value' => false],
            ['facebookAccessToken', 'string'],
            ['facebookAccessToken', 'default', 'value' => ''],
            ['facebookPageId', 'string'],
            ['facebookPageId', 'default', 'value' => ''],
        ];

        if ($this->facebookOn) {
            $rules = array_merge($rules, [
                ['facebookAccessToken', 'required'],
                ['facebookPageId', 'required'],
            ]);
        }

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $twitter = $this->getTwitterRules();
        $flickr = $this->getflickrRules();
        $instagram = $this->getInstagramRules();
        $youtube = $this->getYoutubeRules();
        $facebook = $this->getFacebookRules();

        return array_merge($twitter, $flickr, $instagram, $youtube, $facebook);
    }
}
