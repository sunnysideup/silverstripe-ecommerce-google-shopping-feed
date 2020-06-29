<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use SilverStripe\Control\ContentNegotiator;
use SilverStripe\Core\Config\Config;
use SilverStripe\View\SSViewer;

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.txt
 * </code>
 */
class TSVGoogleShoppingFeedController extends GoogleShoppingFeedController
{
    /**
     * @var array
     */
    private static $allowed_actions = [
        'index',
    ];

    public function index()
    {
        Config::modify()->update(SSViewer::class, 'source_file_comments', false);
        // We need to override the default content-type
        Config::modify()->update(ContentNegotiator::class, 'enabled', false);
        $filename = 'shoppingfeed';
        $this->getResponse()->addHeader('Content-Type', 'text/tab-separated-values; charset="utf-8"');
        $this->getResponse()->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.txt');
        $this->getResponse()->addHeader('Pragma', 'no-cache');
        $this->getResponse()->addHeader('Expires', 0);
        return [];
    }

    public function TSVOutput()
    {
        $apiClass = Config::inst()->get(GoogleShoppingFeedController::class, 'api_class');
        $apiClass = new $apiClass();
        $data = $apiClass->getTSVData();
        return $this->convertToCSV($data, "\t");
    }

    protected function convertToCSV($rows, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false)
    {
        $delimiter_esc = preg_quote($delimiter, '/');
        $enclosure_esc = preg_quote($enclosure, '/');
        $string = '';
        foreach ($rows as $row) {
            if ($string) {
                $string .= "\r\n";
            }
            $output = [];
            foreach ($row as $field) {
                if (! $field) {
                    $output[] = $enclosure . $field . $enclosure;
                } else {
                    // Enclose fields containing $delimiter, $enclosure or whitespace
                    if ($encloseAll || preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field)) {
                        $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
                    } else {
                        $output[] = $field;
                    }
                }
            }
            $string .= implode($delimiter, $output);
            unset($output);
        }
        return $string;
    }
}
