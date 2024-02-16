# also see Google Shopping Feed for more details: https://support.google.com/merchants/answer/7052112?hl=en

# to customise:

```yml
Sunnysideup\EcommerceGoogleShoppingFeed\Controllers\GoogleShoppingFeedController:
    dependencies:
        dataProviderAPI: '%$App\Provider\CustomGoogleShoppingFeedController'
        # ============= OR ================
        dataProviderAPI: '%$GoogleShoppingFeedDataProviderAPI'

SilverStripe\Core\Injector\Injector:
    GoogleShoppingFeedDataProviderAPI:
        class: App\Provider\CustomGoogleShoppingFeedController
```
