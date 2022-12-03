<?php include_once 'vendor/autoload.php';

use V2\CoffeeShops;
include 'V2/CoffeShops/coffeShop.php';

include 'V2/CoffeShops/menu.php';
include ('V2/CoffeShops/textMenuOutput.php');

$products = [
    ['id'=>1,'name'=>'Negro','price'=>25],['id'=>2,'name'=>'Latte','price'=>35],['id'=>3,'name'=>'Capuchino','price'=>45],
];

$shop = new V2\CoffeShops\coffeShop\CoffeeShop();
$shop->addProducts($products);

$menu = new V2\CoffeShops\menu\Menu();
echo $menu->view($shop->getProducts(),new V2\CoffeShops\TextMenuOutput());

?>