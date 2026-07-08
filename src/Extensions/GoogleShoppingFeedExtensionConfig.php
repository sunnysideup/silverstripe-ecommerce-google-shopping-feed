<?php

declare(strict_types=1);

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

use SilverStripe\Core\Extension;
use Sunnysideup\Ecommerce\Pages\Product;
use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\FieldList;
use Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory;

/**
 * Class \Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension
 *
 * @property Product|GoogleShoppingFeedExtension $owner
 * @property bool $HideFromShoppingFeed
 * @property string $MPN
 * @property int $GoogleProductCategoryID
 * @method GoogleProductCategory GoogleProductCategory()
 */
class GoogleShoppingFeedExtensionConfig extends Extension
{
    /**
     * @var array
     */
    private static $db = [
        'MinimumPriceForGoogleShoppingFeed' => 'Currency',
    ];

    /**
     * Add the fields to "CMSFields" (if we are not using settings fields).
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.GoogleFeed',
            [
                CurrencyField::create('MinimumPriceForGoogleShoppingFeed', 'Minimum Price for Feed')
                    ->setDescription('Minimum Product Price for Google Shopping Feed (leave empty to include all products)'),
            ]
        );
    }
}
