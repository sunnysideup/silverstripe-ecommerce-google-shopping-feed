<?php

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.xml
 * </code>
 *
 * @package googlesitemaps
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
     * for a class
     *
     * @return mixed
     */
    public function index()
    {
        Config::inst()->update('SSViewer', 'set_source_file_comments', false);

        $this->getResponse()->addHeader(
            'Content-Type',
            'application/xml; charset="utf-8"'
        );
        $this->getResponse()->addHeader(
            'X-Robots-Tag',
            'noindex'
        );

        $currency = EcommerceCurrency::default_currency_code();

        $apiClass = Config::inst()->get(GoogleShoppingFeedController::class, 'api_class');
        $apiClass = new $apiClass();

        return [
            'SiteConfig' => SiteConfig::current_site_config(),
            'Items' => $apiClass->getArrayList(),
            'Currency' => $currency,
        ];
    }
}
