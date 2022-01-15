<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use SilverStripe\Control\Controller;
use Sunnysideup\EcommerceGoogleShoppingFeed\Api\ProductCollectionForGoogleShoppingFeed;
use SilverStripe\Control\ContentNegotiator;
use SilverStripe\Core\Config\Config;

use SilverStripe\Core\ClassInfo;
use SilverStripe\View\SSViewer;

use SilverStripe\SiteConfig\SiteConfig;
use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.xml
 * </code>
 */
class GoogleShoppingFeedController extends Controller
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'index',
    ];

    private static $api_class = ProductCollectionForGoogleShoppingFeed::class;


    protected function getFileName() : string
    {
        return 'shoppingfeed.'.$this->getExtension();
    }


    /**
     * Specific controller action for displaying a particular list of links
     * for a class.
     *
     * @return mixed
     */
    public function index()
    {
        Config::modify()->update(SSViewer::class, 'set_source_file_comments', false);
        Config::modify()->update(ContentNegotiator::class, 'enabled', false);
        // response header
        $header = $this->getResponse();
        $header->addHeader('Pragma', 'no-cache');
        $header->addHeader('Expires', 0);
        $header->addHeader('Content-Type', $this->getContentType());
        $header->addHeader('Content-Disposition', 'attachment; filename=' . $this->getFileName());
        $header->addHeader('X-Robots-Tag','noindex');
        return $this->renderWith(ClassInfo::shortName(static::class));
    }
}
