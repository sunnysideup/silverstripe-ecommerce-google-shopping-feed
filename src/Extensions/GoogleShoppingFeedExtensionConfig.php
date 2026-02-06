<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

use SilverStripe\Forms\CurrencyField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory;

/**
 * Class \Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension
 *
 * @property \Sunnysideup\Ecommerce\Pages\Product|\Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension $owner
 * @property bool $HideFromShoppingFeed
 * @property string $MPN
 * @property int $GoogleProductCategoryID
 * @method \Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory GoogleProductCategory()
 */
class GoogleShoppingFeedExtensionConfig extends DataExtension
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
