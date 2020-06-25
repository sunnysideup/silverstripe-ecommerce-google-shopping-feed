<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

use DataExtension;
use FieldList;
use CheckboxField;
use TextField;
use AutoCompleteField;



/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD:  extends DataExtension (ignore case)
  * NEW:  extends DataExtension (COMPLEX)
  * EXP: Check for use of $this->anyVar and replace with $this->anyVar[$this->owner->ID] or consider turning the class into a trait
  * ### @@@@ STOP REPLACEMENT @@@@ ###
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
        'GoogleProductCategory' => 'GoogleProductCategory',
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
                TextField::create('MPN'),
                AutoCompleteField::create(
                    'GoogleProductCategoryID',
                    $this->owner->fieldLabel('GoogleProductCategory'),
                    '',
                    null,
                    null,
                    'GoogleProductCategory',
                    'Title'
                ),
            ]
        );
    }
}

