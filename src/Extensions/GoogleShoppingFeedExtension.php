<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory;
use TractorCow\AutoComplete\AutoCompleteField;

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
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.GoogleShoppingFeed',
            [
                CheckboxField::create('HideFromShoppingFeed'),
                TextField::create('MPN', 'MPN / SKU'),
                AutoCompleteField::create(
                    'GoogleProductCategoryID',
                    $this->owner->fieldLabel('GoogleProductCategory'),
                    '',
                    GoogleProductCategory::class,
                    'Title'
                ),
            ]
        );
    }
}
