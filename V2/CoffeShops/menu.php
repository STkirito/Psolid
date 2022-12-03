<?php
namespace V2\CoffeShops\menu;
include 'V2/CoffeShops/menuOutput.php';
class Menu {

    public function view($products, V2\CoffeShops\MenuOutput $output){
        return $output -> MenuOutput.output($products);
    }
}
