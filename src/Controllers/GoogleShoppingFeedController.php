<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use SilverStripe\Control\Controller;
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
    }
}
