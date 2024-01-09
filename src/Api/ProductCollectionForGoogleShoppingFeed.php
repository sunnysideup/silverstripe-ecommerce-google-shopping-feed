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
    public function getArrayFull(): array
    {
        $defaultImageLink = EcommerceDBConfig::current_ecommerce_db_config()->DefaultProductImage()->Filename;
        $baseURL = Director::absoluteBaseURL();
        $assetUrl = Controller::join_links($baseURL, 'assets');
        // array
        $array = [];
        // products
        $products = $this->getArrayBasic();
        foreach ($products as $product) {
            $className = $product['ClassName'] ?? Product::class;
            if (method_exists($className, 'get_data_for_google_shopping_feed')) {
                $productArray = $className::get_data_for_google_shopping_feed($product['ID']);
            } else {

                $internalItemID = ($product['InternalItemID']);
                $productTitle = ($product['ProductTitle']);
                $price = $this->priceToGooglePrice($product['Price']);
                $link = Controller::join_links($baseURL, ($product['InternalItemID']));
                $availability = 'in_stock';
                $condition = 'new';
                $imageLink = Controller::join_links($assetUrl, ($product['FileFilename'] ?: $defaultImageLink));
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
            }
            // ensure special chars are converted to HTML entities for XML output
            // do other stuff!
            $array[] = $productArray;
        }
        return $array;
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
