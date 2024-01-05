<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use DOMDocument;
use SilverStripe\Control\ContentNegotiator;
use SilverStripe\Control\Controller;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\View\SSViewer;
use SimpleXMLElement;
use Sunnysideup\Download\Control\DownloadFile;
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


    protected function getDataAsXMLInner(array $data): string
    {


        $xmlString =
            '<?xml version="1.0" encoding="UTF-8"?>
                <rss xmlns:pj="https://schema.prisjakt.nu/ns/1.0" xmlns:g="http://base.google.com/ns/1.0" version="3.0">
                <channel>
                    <title>Prisjakt Minimal Example Feed</title>
                    <description>This is an example feed with the minimal values required</description>
                    <link>https://schema.prisjakt.nu</link>
                </channel>
            </rss>
            ';
        $xml = simplexml_load_string($xmlString);

        // Adding item under channel
        $channel = $xml->channel;
        foreach ($data as $entry) {
            $item = $channel->addChild('item');
            $this->addArrayToXml($entry, $item);
        }
        return $this->formatXml($xml->asXML());
    }

    protected function addArrayToXml($item, SimpleXMLElement $xml)
    {
        foreach ($item as $key => $value) {
            // Add child with namespace
            if (is_array($value)) {
                $subnode = $xml->addChild($key, null, 'http://base.google.com/ns/1.0');
                $this->addArrayToXml($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value), 'http://base.google.com/ns/1.0');
            }
        }
    }

    private function formatXml(string $xmlContent): string
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xmlContent);

        return $dom->saveXML();
    }

}
