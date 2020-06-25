<?php

class ProductCollectionForGoogleShoppingFeed extends ProductCollection
{
    public function getArrayList(): ArrayList
    {
        $arrayList = new ArrayList();

        $products = parent::getArrayBasic();

        foreach ($products as $id => $className) {
            $productArray = [];
            if (method_exists($className, 'get_data_for_google_shopping_feed')) {
                $productArray = $className::get_data_for_google_shopping_feed($id);
            }

            if (! empty($productArray)) {
                $arrayList->push(
                    ArrayData::create(
                        $productArray
                    )
                );
            }
        }

        return $arrayList;
    }

    public function getTSVData(): array
    {
        $array = [];
        $array[] = [
            'id', //1
            'title', //2
            'description', //3
            'google product category', //4
            'link', //6
            'image link', //7
            'condition', //8
            'availability', //9
            'price', //10
            'brand', //11
            'mpn', //12
            'custom label 1', //13
        ];

        $products = parent::getArrayBasic();

        foreach ($products as $id => $className) {
            $productArray = [];
            if (method_exists($className, 'get_data_for_google_shopping_feed')) {
                $productArray = $className::get_data_for_google_shopping_feed($id);
            }

            if (! empty($productArray)) {
                $array[] = $productArray;
            }
        }

        return $array;
    }

    public function getArrayFull(): array
    {
        return [];
    }
}

