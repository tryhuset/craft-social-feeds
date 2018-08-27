<?php
/**
 * Social feeds plugin for Craft CMS 3.x
 *
 * Utilizes json api to get latest social feeds
 *
 * @link      https://apt.no/
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\socialfeeds;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\web\View;
use craft\events\TemplateEvent;
use craft\events\RegisterCacheOptionsEvent;
use craft\utilities\ClearCaches;
use craft\web\twig\variables\CraftVariable;
use yii\base\Event;

use apt\socialfeeds\models\Settings;
use apt\socialfeeds\models\Variable;
use apt\socialfeeds\services;
use apt\socialfeeds\twigextensions\TwigExtension;

/**
 * Class SocialFeeds
 *
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 *
 */
class SocialFeeds extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var SocialFeeds
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Craft::$app->view->registerTwigExtension(new TwigExtension());

        $this->setComponents([
            'facebook' => new services\Facebook([
                'activated' => ($this->settings->facebook && $this->settings->facebookOn),
                'appId' => $this->settings->facebookAppId,
                'appSecret'=> $this->settings->facebookAppSecret,
                'pageId' => $this->settings->facebookPageId,
                'cache' => Craft::$app->cache,
                'state' => $this->settings->getFacebookStateString(),
            ]),
            'instagram' => new services\Instagram([
                'activated' => ($this->settings->instagram && $this->settings->instagramOn),
                'id' => $this->settings->instagramId,
                'token' => $this->settings->instagramToken,
                'cache' => Craft::$app->cache,
                'state' => $this->settings->getInstagramStateString(),
            ]),
            'twitter' => new services\Twitter([
                'settings' => $this->settings,
                'activated' => ($this->settings->twitter && $this->settings->twitterOn),
                'consumerKey' => $this->settings->twitterConsumerKey,
                'consumerSecret' => $this->settings->twitterConsumerSecret,
                'token' => $this->settings->twitterToken,
                'tokenSecret' => $this->settings->twitterTokenSecret,
                'screenName' => $this->settings->twitterScreenName,
                'cache' => Craft::$app->cache,
                'state' => $this->settings->getTwitterStateString(),
            ]),
            'youtube' => new services\Youtube([
                'activated' => ($this->settings->youtube && $this->settings->youtubeOn),
                'type' => $this->settings->youtubeType,
                'playlist' => $this->settings->youtubePlaylist,
                'channel' => $this->settings->youtubeChannel,
                'key' => $this->settings->youtubeKey,
                'cache' => Craft::$app->cache,
                'state' => $this->settings->getYoutubeStateString(),
            ]),
            'flickr' => new services\Flickr([
                'activated' => ($this->settings->flickr && $this->settings->flickrOn),
                'id' => $this->settings->flickrId,
                'cache' => Craft::$app->cache,
                'state' => $this->settings->getYoutubeStateString(),
            ]),
        ]);

        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $variable = $event->sender;
            $variable->set('aptSocialFeeds', Variable::class);
        });

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'social-feeds/twitter';
                $event->rules['siteActionTrigger2'] = 'social-feeds/flickr';
                $event->rules['siteActionTrigger3'] = 'social-feeds/instagram';
                $event->rules['siteActionTrigger4'] = 'social-feeds/youtube';
                $event->rules['siteActionTrigger5'] = 'social-feeds/facebook';
            }
        );

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function(TemplateEvent $e) {
            if (array_key_exists('plugin', $e->variables)) {
                if (
                    $e->template === 'settings/plugins/_settings' &&
                    $e->variables['plugin'] === $this
                ) {
                    $tabs = $this->getTabs();
                    if (count($tabs) > 0) {
                        $e->variables['tabs'] = $tabs;
                    }
                }
            }
        });

        Craft::info(
            Craft::t(
                'apt-social-feeds',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    protected function getTabs() : array
    {
        $settings = $this->getSettings();
        $tabs = [];
        if ($settings->facebook) {
            $tabs[] = ['label' => 'Facebook', 'url' => '#settings-tab-facebook'];
        }
        if ($settings->youtube) {
            $tabs[] = ['label' => 'Youtube', 'url' => '#settings-tab-youtube'];
        }
        if ($settings->twitter) {
            $tabs[] = ['label' => 'Twitter', 'url' => '#settings-tab-twitter'];
        }
        if ($settings->instagram) {
            $tabs[] = ['label' => 'Instagram', 'url' => '#settings-tab-instagram'];
        }
        if ($settings->flickr) {
            $tabs[] = ['label' => 'Flickr', 'url' => '#settings-tab-flickr'];
        }
        return $tabs;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'apt-social-feeds/settings',
            [
                'settings' => $this->getSettings(),
                'plugin' => $this,
            ]
        );
    }
}
