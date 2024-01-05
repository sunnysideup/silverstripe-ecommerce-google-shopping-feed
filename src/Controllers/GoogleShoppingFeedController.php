<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use SilverStripe\Control\ContentNegotiator;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;
use Sunnysideup\Download\DownloadFile;
use Sunnysideup\EcommerceGoogleShoppingFeed\Api\ProductCollectionForGoogleShoppingFeed;

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.xml
 * </code>
 *
 */
class GoogleShoppingFeedController extends DownloadFile
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'index',
    ];


    private static $dependencies = [
        'dataProviderAPI' => '%$' . ProductCollectionForGoogleShoppingFeed::class,
    ];


    protected function getFileName(): string
    {
        return 'shoppingfeed.xml';
    }

    protected function getContentType(): string
    {
        return 'application/xml; charset="utf-8"';
    }

    public function SiteConfig()
    {
        return SiteConfig::current_site_config();
    }

    public function Items()
    {
        return $$this->dataProviderAPI->getArrayList();
    }

}
