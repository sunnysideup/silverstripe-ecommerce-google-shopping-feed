<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Api;

use Real\Validator\Gtin\Factory;
use Real\Validator\Gtin\NonNormalizable;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DB;
use SilverStripe\View\ArrayData;
use Sunnysideup\Ecommerce\Api\Converters\CsvFunctionality;
use Sunnysideup\Ecommerce\Api\ProductCollection;
use Sunnysideup\Ecommerce\Model\Config\EcommerceDBConfig;
use Sunnysideup\Ecommerce\Pages\Product;

class EanChecker
{
    public static function is_valid_ean(string $ean): bool
    {
        if (empty($ean)) {
            return false;
        }
        try {
            $gtin = Factory::create($ean);
        } catch (NonNormalizable $e) {
            return false;
        }
        return $gtin->checkDigit();
    }
    public static function format_ean(string $ean): string
    {
        if (self::is_valid_ean($ean)) {
            try {
                $gtin = Factory::create($ean);
                if ($gtin->checkDigit()) {
                    return $gtin->padded();
                }
            } catch (NonNormalizable $e) {
                // do nothing
            }
        }
        return '';
    }
}
