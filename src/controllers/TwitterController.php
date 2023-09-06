<?php
/**
 * Social feeds plugin for Craft CMS 3.x
 *
 * Utilizes json api to get latest social feeds
 *
 * @link      https://apt.no
 * @copyright Copyright (c) 2018 Thomas Sømoen
 */

namespace apt\socialfeeds\controllers;

use apt\socialfeeds\SocialFeeds;

use Craft;
use craft\web\Controller;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class TwitterController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected array|int|bool $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $service = SocialFeeds::getInstance()->twitter;
        $limit = Craft::$app->request->getParam('limit', 6);
        $feed = $service->getFeed($limit, true);

        if (array_key_exists('status', $feed)) {
            $response = Craft::$app->getResponse();
            $response->headers->set('Status', $feed['status']);
        }

        return $this->asJson($feed);
    }
}
