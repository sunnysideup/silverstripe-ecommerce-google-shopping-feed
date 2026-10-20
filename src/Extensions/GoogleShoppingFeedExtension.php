<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\EcommerceGoogleShoppingFeed\Api\ProductCollectionForGoogleShoppingFeed;

/**
 * Class \Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension
 *
 * @property \Sunnysideup\Ecommerce\Pages\Product|\Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension $owner
 * @property bool $HideFromShoppingFeed
 * @property string $MPN
 */
class GoogleShoppingFeedExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'HideFromShoppingFeed' => 'Boolean',
        'MPN' => 'Varchar(255)',
    ];

    private static $field_labels = [
        'MPN' => 'MPN / SKU',
    ];

    /**
     * Add the fields to "CMSFields" (if we are not using settings fields).
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.GoogleFeed',
            [
                CheckboxField::create('HideFromShoppingFeed'),
                TextField::create('MPN', 'MPN / SKU'),
            ]
        );
    }

    public function MyGoogleFeedXmlArray(): array
    {
        $obj = Injector::inst()->get(ProductCollectionForGoogleShoppingFeed::class);
        return array_pop(
            $obj->oneProductRaw2Array(
                $obj->getArrayFull('"InternalItemID" = \'' . $this->getOwner()->InternalItemID . '\'')
            )
        );
    }
}
