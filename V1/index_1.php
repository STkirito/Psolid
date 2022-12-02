<?php include_once 'vendor/autoload.php';

use V1\CoffeeShops;
include 'V1/CoffeShops/coffeShop.php';
include 'V1/CoffeShops/menu.php';
$products = [
    ['id'=>1,'name'=>'Negro','price'=>25],['id'=>2,'name'=>'Latte','price'=>35],['id'=>3,'name'=>'Capuchino','price'=>45],
];

$shop = new V1\CoffeShops\coffeShop\CoffeeShop();
$shop->addProducts($products);

$menu = new V1\CoffeShops\menu\Menu();
echo $menu->view($shop->getProducts());

?>