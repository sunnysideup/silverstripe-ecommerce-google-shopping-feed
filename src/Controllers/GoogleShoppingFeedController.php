<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use DOMDocument;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\SiteConfig\SiteConfig;
use SimpleXMLElement;
use Sunnysideup\Download\Control\DownloadFile;
use Sunnysideup\Download\Model\CachedDownload;
use Sunnysideup\EcommerceGoogleShoppingFeed\Api\ProductCollectionForGoogleShoppingFeed;

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.xml
 * </code>
 */
class GoogleShoppingFeedController extends DownloadFile
{
    public const KEY_SUFFIX_DELIMITER_FOR_XML = '___';
    
    public const FILE_GET_VAR_GLUE = '---';

    public const FILE_GET_VAR_GLUE_INNER = '___';

    private static $url_segment = 'shoppingfeed';

    /**
     * @var array
     */
    private static $allowed_actions = [
        'index' => true,
    ];

    private static $dependencies = [
        'dataProviderAPI' => '%$' . ProductCollectionForGoogleShoppingFeed::class,
    ];

    // Send every URL variation to index() so 'parentid-123' is never
    // treated as an action name (which would 404).
    private static $url_handlers = [
        ''         => 'index',
        '$segment' => 'index',
    ];

    protected $useTemplate = false;

    protected function getFileName(): string
    {
        return static::get_link($this->getGetVars());
    }

    protected function getContentType(): string
    {
        return 'application/xml; charset="utf-8"';
    }

    protected function getTitle(): string
    {
        return 'Google Shopping Feed (' . $this->getProductCount() . ')';
    }

    protected function getProductCount()
    {
        return count($this->getRawDataForGoogleShoppingFeed());
    }

    protected function getSchema(): string
    {
        return '<rss xmlns:g="http://base.google.com/ns/1.0" version="3.0">';
    }

    public function SiteConfig()
    {
        return SiteConfig::current_site_config();
    }

    public function getFileData(): string
    {
        if ($this->useTemplate) {
            return parent::getFileData();
        } else {
            return CachedDownload::inst($this->getFilename(), $this->getTitle())
                ->getData(
                    function () {
                        return $this->getDataAsXMLInner($this->getRawDataForGoogleShoppingFeed());
                    },
                    $this->getFileName(),
                );
        }
    }

    private function getDataProviderAPI(): ProductCollectionForGoogleShoppingFeed
    {
        $filter = $this->getGetVars();
        if (!empty($filter)) {
            $this->dataProviderAPI->setAdditionalPredeterminedFilters($filter);
        }
        return $this->dataProviderAPI;
    }

    public function Items()
    {
        return $this->getDataProviderAPI()->getArrayList();
    }

    protected function getDataAsXMLInner(array $data): string
    {
        $xmlString =
            '<?xml version="1.0" encoding="UTF-8"?>
                ' . $this->getSchema() . '
                <channel>
                    <title>' . $this->SiteConfig()->Title . '</title>
                    <description>' . $this->SiteConfig()->Tagline . '</description>
                    <link>' . Director::absoluteBaseURL() . '</link>
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

    protected array|null $rawDataForGoogleShoppingFeed = null;

    public function getRawDataForGoogleShoppingFeed(): array
    {
        if ($this->rawDataForGoogleShoppingFeed === null) {
            $this->rawDataForGoogleShoppingFeed = $this->getDataProviderAPI()->getArrayFull(null);
        }
        return $this->rawDataForGoogleShoppingFeed;
    }

    protected function addArrayToXml(array $data, SimpleXMLElement $item): void
    {
        foreach ($data as $key => $value) {
            // additional_image_link___2  =>  additional_image_link
            $delimPos    = strpos((string) $key, self::KEY_SUFFIX_DELIMITER_FOR_XML);
            $elementName = $delimPos === false
                ? (string) $key
                : substr((string) $key, 0, $delimPos);

            if (is_array($value)) {
                $child = $item->addChild($elementName);
                $this->addArrayToXml($value, $child);
            } else {
                $item->addChild($elementName, htmlspecialchars((string) $value));
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

    protected function getMaxAgeInMinutes(): ?int
    {
        return 60; // set to null to use default
    }

    protected function getDeleteOnFlush(): ?bool
    {
        return false; // set to null to use default
    }

    protected function getGetVars(?array $vars = null): array
    {
        $vars = $vars ? $vars : $this->getVarsFromSegment($this->getRequest()->param('segment'));
        if (is_array($vars) && !empty($vars)) {
            foreach ($vars as $key => $value) {
                if ($key !== 'parentid' && $key !== 'internalitemids') {
                    unset($vars[$key]);
                }
            }
            return $vars;
        }
        return [];
    }

    protected function getVarsFromSegment(?string $segment): array
    {
        if (! $segment) {
            return [];
        }
        $parts = explode(static::FILE_GET_VAR_GLUE, $segment);
        $result = [];
        foreach ($parts as $part) {
            $innerParts = explode(static::FILE_GET_VAR_GLUE_INNER, $part);
            if (count($innerParts) === 2) {
                $result[$innerParts[0]] = $innerParts[1];
            }
        }
        return $result;
    }

    public static function get_link(array $vars): string
    {
        return Config::inst()->get(static::class, 'url_segment') . (self::get_params_as_string_for_file_name($vars)) . '.xml';
    }

    protected static function get_params_as_string_for_file_name(array $vars): string
    {
        if (!empty($vars)) {
            $stringArray = [];
            foreach ($vars as $key => $value) {
                $stringArray[] = $key .static::FILE_GET_VAR_GLUE_INNER. preg_replace('/[^A-Za-z0-9._-]+/', '', $value);
            }
            $safe = implode(static::FILE_GET_VAR_GLUE, $stringArray);
            return '/' . $safe;
        }
        return '';
    }

}
