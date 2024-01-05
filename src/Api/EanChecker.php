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
use Sunnysideup\Ecommerce\Pages\Product;

class EanChecker
{
    public static function is_valid_ean(string $ean): bool
    {
        if(strlen($ean) !== 13) {
            return false;
        }
        if (!preg_match("/^[0-9]{13}$/", $ean)) {
            return false; // EAN must be 13 digits
        }

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $ean[$i] * (($i % 2 == 0) ? 1 : 3);
        }

        $checkDigit = 10 - ($sum % 10);
        if ($checkDigit == 10) {
            $checkDigit = 0;
        }

        // Return true if the last digit matches the check digit
        return ((int) $checkDigit == (int) $ean[12]);
    }
}
