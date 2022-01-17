<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use SilverStripe\Core\Config\Config;

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

    public function TSVOutput()
    {
        $apiClass = Config::inst()->get(GoogleShoppingFeedController::class, 'api_class');
        $apiClass = new $apiClass();

        $data = $apiClass->getTSVData();

        return $this->convertToCSV($data, "\t");
    }

    protected function getExtension(): string
    {
        return 'txt';
    }

    protected function getContentType()
    {
        return 'text/tab-separated-values; charset="utf-8"';
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
                } elseif ($encloseAll || preg_match("/(?:{$delimiter_esc}|{$enclosure_esc}|\\s)/", $field)) {
                    $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
                } else {
                    $output[] = $field;
                }
            }
            $string .= implode($delimiter, $output);
            unset($output);
        }

        return $string;
    }
}
