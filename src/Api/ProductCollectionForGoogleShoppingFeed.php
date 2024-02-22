<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Api;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\View\ArrayData;
use Sunnysideup\Ecommerce\Api\Converters\CsvFunctionality;
use Sunnysideup\Ecommerce\Api\ProductCollection;
use Sunnysideup\Ecommerce\Model\Config\EcommerceDBConfig;
use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;
use Sunnysideup\Ecommerce\Pages\Product;

class ProductCollectionForGoogleShoppingFeed extends ProductCollection
{
    protected $defaultImageLink = '';
    protected $baseURL = '';
    protected $assetsUrl = '';


    public function __construct()
    {
        $this->defaultImageLink = EcommerceDBConfig::current_ecommerce_db_config()->DefaultProductImage()->Link();
        $this->baseURL = Director::absoluteBaseURL();
        $this->assetsUrl = Director::baseURL() . 'assets/';
    }

    public function getArrayFull(?string $where = ''): array
    {
        $array = [];
        $products = $this->getArrayBasic($where);
        foreach ($products as $productRaw) {
            $className = $productRaw['ClassName'] ?? Product::class;
            if (method_exists($className, 'get_data_for_google_shopping_feed')) {
                $productArray = $className::get_data_for_google_shopping_feed($productRaw['ID']);
            } else {
                $productArray = $this->oneProductRaw2Array($productRaw);
            }
            // ensure special chars are converted to HTML entities for XML output
            // do other stuff!
            if(!empty($productArray)) {
                $array[] = $productArray;
            }
        }
        return $array;
    }

    public function oneProductRaw2Array(array $productRaw): array
    {
        $internalItemID = ($productRaw['InternalItemID']);
        $productTitle = $productRaw['ProductTitle'] ?? $productRaw['Title'];
        $price = $this->priceToGooglePrice($productRaw['Price']);
        $link = Controller::join_links($this->baseURL, ($productRaw['InternalItemID']));
        $availability = 'in_stock';
        $condition = 'new';
        $imageLink = Controller::join_links($this->assetsUrl, ($productRaw['FileFilename'] ?: $this->defaultImageLink));
        $productArray = [
            'id' => $internalItemID, //1. Your-item-number
            'title' => $productTitle, //2. Product-name
            'price' => $price, //3. price-including-gst
            'link' => $link, //4. link
            'availability' => $availability, //5. stock status
            'condition' => $condition, //6. condition
            'image_link' => $imageLink, //7. image_link
            'google_product_category' => 'TBC',
        ];
        foreach($productArray as $key => $value) {
            $productArray[$key] = ($value);
        }
        return $productArray;
    }

    protected static $currency = '';

    protected function priceToGooglePrice(float $price)
    {
        if(!self::$currency) {
            self::$currency = strtoupper(EcommerceCurrency::default_currency_code());
        }

        return number_format($price, 2, '.', '') . ' ' . strtoupper(self::$currency);
    }


    public function getSQL(?string $where = ''): string
    {
        return '
            SELECT
                "SiteTree_Live"."ID" ProductID,
                "SiteTree_Live"."Title" ProductTitle,
                "Product_Live"."InternalItemID",
                "Product_Live"."Price",
                "File"."FileFilename",
                "ParentSiteTree"."Title" as ParentTitle
            FROM
                "SiteTree_Live"
            INNER JOIN
                "SiteTree_Live" AS ParentSiteTree ON "ParentSiteTree"."ID" = "SiteTree_Live"."ParentID"
            INNER JOIN
                "Product_Live" ON "SiteTree_Live"."ID" = "Product_Live"."ID"
            INNER JOIN
                "Product_ProductGroups" ON "Product_Live"."ID" = "Product_ProductGroups"."ProductID"
            INNER JOIN
                "ProductGroup_Live" ON "Product_ProductGroups"."ProductGroupID" = "ProductGroup_Live"."ID"

            LEFT JOIN
                "File" ON "Product_Live"."ImageID" = "File"."ID"
            WHERE
                ' . $where . '
                "Product_Live"."HideFromShoppingFeed" = 0
                AND
                "Product_Live"."AllowPurchase" = 1
            ' . (Director::isDev() ? 'ORDER BY RAND() LIMIT 10' : 'ORDER BY "SiteTree_Live"."ID" DESC') . '
                ;
        ';

    }
}
