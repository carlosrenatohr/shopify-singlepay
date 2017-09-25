<?php

require 'vendor/autoload.php';
require 'env.php';
use Shopify\ShopifyClient;

//$client = new ShopifyClient('c0c10dfffbba236449b60e673084403d', "sucstudev");
//$products = $client->products->readList();

$shop = $_GET['shop'];
$scopes = "read_orders,read_products,write_products";
$install_url = "http://{$shop}/admin/oauth/authorize?client_id=". API_KEY. "&scope={$scopes}&redirect_uri=".APP_URL . 'auth.php';
header("Location: ". $install_url);