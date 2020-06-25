2020-06-26 07:42

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce-google-shopping-feed
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/ecommerce-google-shopping-feed/ecommerce-google-shopping-feed  --root-dir=/var/www/upgrades/ecommerce-google-shopping-feed --write -vvv
Writing changes for 8 files
Running upgrades on "/var/www/upgrades/ecommerce-google-shopping-feed/ecommerce-google-shopping-feed"
[2020-06-26 07:42:07] Applying UpdateConfigClasses to shoppingfeed.yml...
[2020-06-26 07:42:07] Applying UpdateConfigClasses to routes.yml...
[2020-06-26 07:42:07] Applying RenameClasses to XMLGoogleShoppingFeedController.php...
[2020-06-26 07:42:07] Applying ClassToTraitRule to XMLGoogleShoppingFeedController.php...
[2020-06-26 07:42:07] Applying RenameClasses to GoogleShoppingFeedController.php...
[2020-06-26 07:42:07] Applying ClassToTraitRule to GoogleShoppingFeedController.php...
[2020-06-26 07:42:07] Applying RenameClasses to TSVGoogleShoppingFeedController.php...
[2020-06-26 07:42:07] Applying ClassToTraitRule to TSVGoogleShoppingFeedController.php...
[2020-06-26 07:42:07] Applying RenameClasses to GoogleShoppingFeedExtension.php...
[2020-06-26 07:42:07] Applying ClassToTraitRule to GoogleShoppingFeedExtension.php...
[2020-06-26 07:42:07] Applying RenameClasses to ProductCollectionForGoogleShoppingFeed.php...
[2020-06-26 07:42:07] Applying ClassToTraitRule to ProductCollectionForGoogleShoppingFeed.php...
[2020-06-26 07:42:07] Applying RenameClasses to GoogleProductCategory.php...
[2020-06-26 07:42:07] Applying ClassToTraitRule to GoogleProductCategory.php...
[2020-06-26 07:42:07] Applying RenameClasses to _config.php...
[2020-06-26 07:42:07] Applying ClassToTraitRule to _config.php...
modified:	_config/shoppingfeed.yml
@@ -3,7 +3,7 @@
 Before: 'app/*'
 After: ['#coreconfig', '#cmsextensions', '#ecommerce']
 ---
+Sunnysideup\Ecommerce\Pages\Product:
+  extensions:
+    - Sunnysideup\EcommerceGoogleShoppingFeed\Extensions\GoogleShoppingFeedExtension

-Product:
-  extensions:
-    - GoogleShoppingFeedExtension

modified:	_config/routes.yml
@@ -3,6 +3,6 @@
 ---
 SilverStripe\Control\Director:
   rules:
-    'shoppingfeed.xml': 'XMLGoogleShoppingFeedController'
-    'shoppingfeed.txt': 'TSVGoogleShoppingFeedController'
+    shoppingfeed.xml: Sunnysideup\EcommerceGoogleShoppingFeed\Controllers\XMLGoogleShoppingFeedController
+    shoppingfeed.txt: Sunnysideup\EcommerceGoogleShoppingFeed\Controllers\TSVGoogleShoppingFeedController


modified:	src/Controllers/XMLGoogleShoppingFeedController.php
@@ -2,9 +2,14 @@

 namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

-use Config;
-use EcommerceCurrency;
-use SiteConfig;
+
+
+
+use SilverStripe\Core\Config\Config;
+use SilverStripe\View\SSViewer;
+use Sunnysideup\Ecommerce\Model\Money\EcommerceCurrency;
+use SilverStripe\SiteConfig\SiteConfig;
+


 /**
@@ -31,7 +36,7 @@
      */
     public function index()
     {
-        Config::modify()->update('SSViewer', 'set_source_file_comments', false);
+        Config::modify()->update(SSViewer::class, 'set_source_file_comments', false);

         $this->getResponse()->addHeader(
             'Content-Type',

Warnings for src/Controllers/XMLGoogleShoppingFeedController.php:
 - src/Controllers/XMLGoogleShoppingFeedController.php:48 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 48

modified:	src/Controllers/GoogleShoppingFeedController.php
@@ -2,8 +2,11 @@

 namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

-use Controller;
-use ProductCollectionForGoogleShoppingFeed;
+
+
+use Sunnysideup\EcommerceGoogleShoppingFeed\Api\ProductCollectionForGoogleShoppingFeed;
+use SilverStripe\Control\Controller;
+


 /**

modified:	src/Controllers/TSVGoogleShoppingFeedController.php
@@ -2,7 +2,11 @@

 namespace Sunnysideup\EcommerceGoogleShoppingFeed\Controllers;

-use Config;
+
+use SilverStripe\Core\Config\Config;
+use SilverStripe\View\SSViewer;
+use SilverStripe\Control\ContentNegotiator;
+


 /**
@@ -32,9 +36,9 @@

     public function index()
     {
-        Config::modify()->update('SSViewer', 'source_file_comments', false);
+        Config::modify()->update(SSViewer::class, 'source_file_comments', false);
         // We need to override the default content-type
-        Config::modify()->update('ContentNegotiator', 'enabled', false);
+        Config::modify()->update(ContentNegotiator::class, 'enabled', false);
         $filename = 'shoppingfeed';
         $this->getResponse()->addHeader('Content-Type', 'text/tab-separated-values; charset="utf-8"');
         $this->getResponse()->addHeader('Content-Disposition', 'attachment; filename=' . $filename . '.txt');

Warnings for src/Controllers/TSVGoogleShoppingFeedController.php:
 - src/Controllers/TSVGoogleShoppingFeedController.php:49 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 49

modified:	src/Extensions/GoogleShoppingFeedExtension.php
@@ -2,11 +2,18 @@

 namespace Sunnysideup\EcommerceGoogleShoppingFeed\Extensions;

-use DataExtension;
-use FieldList;
-use CheckboxField;
-use TextField;
-use AutoCompleteField;
+
+
+
+
+
+use Sunnysideup\EcommerceGoogleShoppingFeed\Model\GoogleProductCategory;
+use SilverStripe\Forms\FieldList;
+use SilverStripe\Forms\CheckboxField;
+use SilverStripe\Forms\TextField;
+use TractorCow\AutoComplete\AutoCompleteField;
+use SilverStripe\ORM\DataExtension;
+



@@ -29,7 +36,7 @@
     ];

     private static $has_one = [
-        'GoogleProductCategory' => 'GoogleProductCategory',
+        'GoogleProductCategory' => GoogleProductCategory::class,
     ];

     /**
@@ -46,11 +53,11 @@
                 TextField::create('MPN'),
                 AutoCompleteField::create(
                     'GoogleProductCategoryID',
-                    $this->owner->fieldLabel('GoogleProductCategory'),
+                    $this->owner->fieldLabel(GoogleProductCategory::class),
                     '',
                     null,
                     null,
-                    'GoogleProductCategory',
+                    GoogleProductCategory::class,
                     'Title'
                 ),
             ]

modified:	src/Api/ProductCollectionForGoogleShoppingFeed.php
@@ -2,9 +2,13 @@

 namespace Sunnysideup\EcommerceGoogleShoppingFeed\Api;

-use ProductCollection;
-use ArrayList;
-use ArrayData;
+
+
+
+use SilverStripe\ORM\ArrayList;
+use SilverStripe\View\ArrayData;
+use Sunnysideup\Ecommerce\Api\ProductCollection;
+


 class ProductCollectionForGoogleShoppingFeed extends ProductCollection

Warnings for src/Api/ProductCollectionForGoogleShoppingFeed.php:
 - src/Api/ProductCollectionForGoogleShoppingFeed.php:48 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 48

 - src/Api/ProductCollectionForGoogleShoppingFeed.php:113 PhpParser\Node\Expr\Variable
 - WARNING: New class instantiated by a dynamic value on line 113

modified:	src/Model/GoogleProductCategory.php
@@ -2,8 +2,11 @@

 namespace Sunnysideup\EcommerceGoogleShoppingFeed\Model;

-use DataObject;
-use DB;
+
+
+use SilverStripe\ORM\DB;
+use SilverStripe\ORM\DataObject;
+


 class GoogleProductCategory extends DataObject

Writing changes for 8 files
✔✔✔