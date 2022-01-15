<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use SilverStripe\Core\Config\Config;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;
use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.xml
 * </code>
 */
class XMLGoogleShoppingFeedController extends GoogleShoppingFeedController
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'index',
    ];

    protected function getExtension() : string
    {
        return 'xml';
    }

    protected function getContentType() : string
    {
        return 'application/xml; charset="utf-8"';
    }

    public function SiteConfig()
    {
        return SiteConfig::current_site_config();
    }

    public function Items()
    {
        $apiClassName = Config::inst()->get(GoogleShoppingFeedController::class, 'api_class');
        $apiClass = new $apiClassName();
        return $apiClass->getArrayList();
    }

}
