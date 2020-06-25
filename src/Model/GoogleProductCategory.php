<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Model;

use DataObject;
use DB;


class GoogleProductCategory extends DataObject
{

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * OLD: private static $db (case sensitive)
  * NEW: 
    private static $table_name = '[SEARCH_REPLACE_CLASS_NAME_GOES_HERE]';

    private static $db (COMPLEX)
  * EXP: Check that is class indeed extends DataObject and that it is not a data-extension!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
    
    private static $table_name = 'GoogleProductCategory';

    private static $db = [
        'GoogleID' => 'Int',
        'Title' => 'Varchar(255)',
    ];

    /**
     * Build the initial list of Categories
     */
    public function RequireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (! GoogleProductCategory::get()->exists()) {
            DB::alteration_message('Creating categories (this may take 5 - 10 mins)', 'created');

            $default_categories = $this->getGoogleCategories();
            $count = 0;

            foreach ($default_categories as $key => $value) {
                $new_cat = GoogleProductCategory::create([
                    'GoogleID' => $key,
                    'Title' => $value,
                ]);

                $new_cat->write();
                $count++;
            }

            DB::alteration_message("Created {$count} Categories", 'created');
        }
    }

    /**
     * Get a list of google shopping categories which are formatted as:
     *
     * Key: ID of category
     * Value: Full name of category
     *
     * @return array
     */
    public function getGoogleCategories()
    {
        // Get a list of Google Categories from the
        // product file.
        $file = dirname(__FILE__) . '/../../thirdparty/google_product_taxonomy.txt';
        $fopen = fopen($file, 'r');
        $fread = fread($fopen, filesize($file));
        fclose($fopen);
        $result = [];

        foreach (explode("\n", $fread) as $string) {
            $exploded = explode(' - ', $string);
            if ($string && count($exploded) === 2) {
                $result[$exploded[0]] = $exploded[1];
            }
        }

        return $result;
    }

    public function canDelete($member = null, $context = [])
    {
        return false;
    }
}
