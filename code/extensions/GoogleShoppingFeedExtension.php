<?php

class GoogleShoppingFeedExtension extends DataExtension
{
    
    /**
     * @var array
     */
    private static $db = [
        "HideFromShoppingFeed" => "Boolean",
        "MPN" =>  "Varchar(255)",
    ];

    private static $has_one = [
        "GoogleProductCategory" => "GoogleProductCategory"
    ];

    /**
     * Add the fields to "CMSFields" (if we are not using settings fields). 
     * 
     * @param FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab(
            'Root.GoogleShoppingFeed',
            [
                CheckboxField::create("HideFromShoppingFeed"),
                TextField::create("MPN"),
                AutoCompleteField::create(
                    'GoogleProductCategoryID',
                    $this->owner->fieldLabel("GoogleProductCategory"),
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
