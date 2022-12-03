<?php


namespace V2\CoffeeShops\jsonMenuOutput;

include('V2/CoffeShops/menuOutput.php');
class JsonMenuOutput implements MenuOutput
{
    public function output($products){

        return $menu = json_encode($products);
    }
}