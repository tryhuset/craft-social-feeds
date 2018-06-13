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

use apt\socialfeeds\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use craft\web\View;
use craft\events\TemplateEvent;

use yii\base\Event;

use apt\socialfeeds\services;

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
        require_once __DIR__ . '/../vendor/autoload.php';
        parent::init();
        self::$plugin = $this;

        $this->setComponents([
            'flickr' => services\Flickr::class,
            'facebook' => services\Facebook::class,
            'instagram' => services\Instagram::class,
            'twitter' => services\Twitter::class,
            'youtube' => services\Youtube::class,
        ]);

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
                    $e->variables['tabs'] = [
                        ['label' => 'Facebook', 'url' => '#settings-tab-facebook'],
                        ['label' => 'Youtube', 'url' => '#settings-tab-youtube'],
                        ['label' => 'Twitter', 'url' => '#settings-tab-twitter'],
                        ['label' => 'Instagram', 'url' => '#settings-tab-instagram'],
                        ['label' => 'Flickr', 'url' => '#settings-tab-flickr'],
                    ];
                }
            }
        });

        Craft::info(
            Craft::t(
                'social-feeds',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
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
            'social-feeds/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
