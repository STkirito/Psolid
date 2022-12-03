<?php

namespace V2\CoffeShops\coffeShop;

class CoffeeShop
{
    protected $products = [];

    public function addProducts($products = []){
        $this->products = $products;
    }

    public function getProducts(){
        return $this->products;
    }

    public function removeProducts($id){

    }

    
    
}