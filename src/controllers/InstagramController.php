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
use GuzzleHttp\Exception\ClientException;

/**
 * @author    Thomas Sømoen
 * @package   SocialFeeds
 * @since     1.0.0
 */
class InstagramController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index'];

    // Public Methods
    // =========================================================================

    /**
     * @return mixed
     */
    public function actionIndex()
    {
        $service = SocialFeeds::getInstance()->instagram;
        $limit = Craft::$app->request->getParam('limit', 6);
        $result = $service->getFeed($limit, true);

        if (array_key_exists('error', $result)) {
            $response = Craft::$app->getResponse();
            $response->headers->set('Status', $result['status']);
        }

        return $this->asJson($result);
    }
}
