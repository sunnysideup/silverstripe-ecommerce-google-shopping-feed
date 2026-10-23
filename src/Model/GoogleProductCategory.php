<?php

namespace Sunnysideup\EcommerceGoogleShoppingFeed\Model;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;

/**
 * Class \Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory
 *
 * @property int $GoogleID
 * @property string $Title
 */
class GoogleProductCategory extends DataObject
{
    private static $table_name = 'GoogleProductCategory';

    private static $relative_path_to_google_categories_file = '/../../thirdparty/google_product_taxonomy.txt';

    private static $db = [
        'GoogleID' => 'Int',
        'Title' => 'Varchar(255)',
    ];
    private static $summary_fields = [
        'GoogleID' => 'Google ID',
        'Title' => 'Title',
    ];
    private static $indexes = [
        'GoogleID' => [
            'type' => 'unique',
            'columns' => ['GoogleID'],
        ]
    ];

    /**
     * Build the initial list of Categories.
     */
    public function RequireDefaultRecords()
    {
        parent::requireDefaultRecords();
        $list = $this->getGoogleCategories();
        $listCount = count($list);
        $dataObjectCount = GoogleProductCategory::get()->count();
        if ($listCount !== $dataObjectCount) {
            DB::alteration_message('Creating categories (this may take 5 - 10 mins)', 'created');
            DB::query('TRUNCATE TABLE "GoogleProductCategory"');
            $count = 0;
            foreach ($list as $key => $value) {
                DB::alteration_message('Creating category ' . $value, 'created');
                $newCat = GoogleProductCategory::create([
                    'GoogleID' => $key,
                    'Title' => $value,
                ]);

                $newCat->write();
                ++$count;
            }
            DB::alteration_message("Created {$count} Categories", 'created');
        }
    }

    /**
     * Get a list of google shopping categories which are formatted as:.
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
        $file = dirname(__FILE__) . $this->config()->get('relative_path_to_google_categories_file');
        $fopen = fopen($file, 'r');
        $fread = fread($fopen, filesize($file));
        fclose($fopen);
        $result = [];

        preg_match_all('/^(\d+)\s+-\s+([^\r\n]+)/m', $fread, $matches);
        $result = array_combine($matches[1], $matches[2]);

        echo '-------------';
        print_r($result);
        echo '-------------';

        return $result;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return false;
    }
}
