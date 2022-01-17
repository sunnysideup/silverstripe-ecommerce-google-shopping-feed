<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use SilverStripe\Control\ContentNegotiator;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\SSViewer;
use Sunnysideup\EcommerceGoogleShoppingFeed\Api\ProductCollectionForGoogleShoppingFeed;

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
        $header->addHeader('X-Robots-Tag', 'noindex');

        return $this->renderWith(ClassInfo::shortName(static::class));
    }

    protected function getFileName(): string
    {
        return 'shoppingfeed.' . $this->getExtension();
    }
}
