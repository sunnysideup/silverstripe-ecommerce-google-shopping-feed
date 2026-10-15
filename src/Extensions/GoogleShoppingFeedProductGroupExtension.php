<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;

/**
 * @property \Sunnysideup\Ecommerce\Pages\ProductGroup|\Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedProductGroupExtension $owner
 * @property string $GoogleProductCategoryOverride
 */
class GoogleShoppingFeedProductGroupExtension extends DataExtension
{
    private static $db = [
        'GoogleProductCategoryOverride' => 'Varchar(255)',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldToTab(
            'Root.GoogleFeed',
            TextField::create(
                'GoogleProductCategoryOverride',
                'Google Product Category Override'
            )->setDescription('Used when products in this group do not have their own Google product category set.')
        );
    }
}
