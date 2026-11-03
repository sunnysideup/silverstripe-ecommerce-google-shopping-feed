<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

use DOMDocument;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\SiteConfig\SiteConfig;
use SimpleXMLElement;
use Sunnysideup\Ecommerce\Model\Address\EcommerceCountry;
use Sunnysideup\Ecommerce\Pages\ProductGroup;
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

    public const GOOGLE_NAMESPACE = 'http://base.google.com/ns/1.0';

    public const RSS_ELEMENTS_WITHOUT_G_PREFIX = ['title', 'description', 'link'];

    public const FILE_GET_VAR_GLUE = '---';

    public const FILE_GET_VAR_GLUE_INNER = '___';

    /**
     * the longest segment we accept, e.g. parentid___123
     */
    public const MAX_SEGMENT_LENGTH = 100;

    /**
     * the only filters we accept in the segment
     */
    public const ALLOWED_FILTER_KEYS = ['parentid', 'internalitemids'];

    public const MAX_INTERNAL_ITEM_IDS = 20;

    private static $url_segment = 'shoppingfeed';

    /**
     * how many new feed files / records one IP address can trigger per window
     */
    private static $new_file_limit = 10;

    private static $new_file_window_in_seconds = 600;

    /**
     * how long a successful feed can be cached by browsers / CDNs.
     */
    private static $cache_max_age_in_seconds = 300;

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

    /**
     * Rejects invalid segments before any record or file is created.
     */
    public function index()
    {
        $segment = $this->getRequest()->param('segment');
        if (null !== $segment && '' !== $segment && ! $this->isValidSegment((string) $segment)) {
            return $this->httpError(404, 'Not found');
        }

        return parent::index();
    }

    protected function getCacheMaxAgeInSeconds(): int
    {
        return (int) $this->config()->get('cache_max_age_in_seconds');
    }

    protected function isValidSegment(string $segment): bool
    {
        if (strlen($segment) > self::MAX_SEGMENT_LENGTH || ! preg_match('/^[A-Za-z0-9._-]+$/', $segment)) {
            return false;
        }
        $parts = explode(static::FILE_GET_VAR_GLUE, $segment);
        foreach ($parts as $part) {
            $innerParts = explode(static::FILE_GET_VAR_GLUE_INNER, $part);
            if (count($innerParts) !== 2) {
                return false;
            }
            [$key, $value] = $innerParts;
            if (! in_array($key, self::ALLOWED_FILTER_KEYS, true) || '' === $value) {
                return false;
            }
            if ('parentid' === $key) {
                if (! ctype_digit($value) || strlen($value) > 9 || (int) $value < 1) {
                    return false;
                }
                // never fall back to the full feed for an unknown group
                if (! ProductGroup::get()->byID((int) $value)) {
                    return false;
                }
            } else {
                $ids = explode('.', $value);
                if (count($ids) > self::MAX_INTERNAL_ITEM_IDS) {
                    return false;
                }
                foreach ($ids as $id) {
                    if (! preg_match('/^[A-Za-z0-9_-]{1,50}$/', $id)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function getFileName(): string
    {
        return static::get_link($this->getGetVars());
    }

    /**
     * Generating a new file is expensive and can not be absorbed by a CDN, so we limit
     * how often one IP address can trigger it.
     */
    protected function throttleNewFiles(string $fileName): void
    {
        $limit = (int) $this->config()->get('new_file_limit');
        if ($limit <= 0) {
            return;
        }
        $exists = CachedDownload::get()->filter(['MyLink' => $fileName])->exists()
            && file_exists(CachedDownload::file_path($fileName));
        if ($exists) {
            return;
        }
        $window = max(1, (int) $this->config()->get('new_file_window_in_seconds'));
        $ip = EcommerceCountry::get_ip();
        $cache = Injector::inst()->get(CacheInterface::class . '.shoppingFeedThrottle');
        $key = 'shoppingfeednew' . md5($ip);
        $entry = $cache->get($key);
        $now = time();
        if (! is_array($entry) || ($entry['start'] ?? 0) + $window <= $now) {
            $entry = ['start' => $now, 'count' => 0];
        }
        if ($entry['count'] >= $limit) {
            $this->httpError(429, 'Too many requests');
        }
        ++$entry['count'];
        $cache->set($key, $entry, max(1, $entry['start'] + $window - $now));
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
        return '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0">';
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
            $fileName = $this->getFileName();
            $this->throttleNewFiles($fileName);

            return CachedDownload::inst($fileName, $this->getTitle())
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

            // standard RSS elements stay un-prefixed, everything else is g:
            $useGoogleNs = !in_array($elementName, self::RSS_ELEMENTS_WITHOUT_G_PREFIX, true);
            $qualifiedName = $useGoogleNs ? 'g:' . $elementName : $elementName;
            $ns = $useGoogleNs ? self::GOOGLE_NAMESPACE : null;

            if (is_array($value)) {
                $child = $item->addChild($qualifiedName, null, $ns);
                $this->addArrayToXml($value, $child);
            } else {
                $item->addChild($qualifiedName, htmlspecialchars((string) $value), $ns);
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
        $segment = (string) $this->getRequest()->param('segment');
        if (strlen($segment) > self::MAX_SEGMENT_LENGTH) {
            return [];
        }
        $vars = $vars ? $vars : $this->getVarsFromSegment($segment);
        if (is_array($vars) && !empty($vars)) {
            foreach ($vars as $key => $value) {
                if (! in_array($key, self::ALLOWED_FILTER_KEYS, true) || ! is_string($value)) {
                    unset($vars[$key]);
                } elseif ('parentid' === $key) {
                    // normalise, so parentid___012 and parentid___12 share one record
                    $vars[$key] = (string) (int) $value;
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
