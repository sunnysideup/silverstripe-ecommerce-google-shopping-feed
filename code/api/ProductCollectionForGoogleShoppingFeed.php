<?php

class ProductCollectionForGoogleShoppingFeed extends ProductCollection
{
    public static function getArrayList()
    {
        $arrayList = new ArrayList();

        $products = parent::getArray();

        foreach($products as $id => $className) {
            $productArray = [];
            if(method_exists($className, 'get_data_for_google_shopping_feed')) {
                $productArray = $className::get_data_for_google_shopping_feed($id);
            }

            if(!empty($productArray)){
                $arrayList->push(
                    ArrayData::create(
                        $productArray    
                    )
                );
            }
        }

        return $arrayList;
    }
}