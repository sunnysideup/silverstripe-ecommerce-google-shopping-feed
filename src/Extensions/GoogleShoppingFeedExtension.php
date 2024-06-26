<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

use DOMDocument;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SimpleXMLElement;
use Sunnysideup\EcommerceGoogleShoppingFeed\Api\ProductCollectionForGoogleShoppingFeed;
use Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory;
use TractorCow\AutoComplete\AutoCompleteField;

/**
 * Class \Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension
 *
 * @property \Sunnysideup\Ecommerce\Pages\Product|\Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension $owner
 * @property bool $HideFromShoppingFeed
 * @property string $MPN
 * @property int $GoogleProductCategoryID
 * @method \Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory GoogleProductCategory()
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

    private static $has_one = [
        'GoogleProductCategory' => GoogleProductCategory::class,
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
                AutoCompleteField::create(
                    'GoogleProductCategoryID',
                    $this->getOwner()->fieldLabel('GoogleProductCategory'),
                    '',
                    GoogleProductCategory::class,
                    'Title'
                ),
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
