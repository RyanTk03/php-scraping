<?php
header('Content-Type: application/json; charset=utf-8');
include_once "simple_html_dom.php";

$SCRAPING_URL = "https://scrapingcourse.com/ecommerce/";

$curl = curl_init($SCRAPING_URL);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$curl_result_content = curl_exec($curl);
if ($curl_result_content === false)
{
    echo "curl error: " . curl_error($curl);
    exit;
}

curl_close($curl);

$html_content = str_get_html($curl_result_content);

$products = $html_content->find(".product");
$productsData = array();

foreach($products as $product)
{
    $name = $product->find(".woocommerce-loop-product__title", 0);
    $img = $product->find(".product-image", 0);
    $price = $product->find(".product-price>bdi", 0);

    if ($name && $img && $price &&
        isset($name->plaintext) &&
        isset($price->plaintext) &&
        isset($img->src)
    )
    {
        $productsData[] = array(
            "Name" => $name->plaintext,
            "Price" => html_entity_decode($price->plaintext),
            "Image Url" => $img->src
        );
    }
}

$DATA_FILE_LOCATION = "products.csv";
$file = fopen($DATA_FILE_LOCATION, "w");

fputcsv($file, array_keys($productsData[0]));

foreach($productsData as $productData)
{
    fputcsv($file, $productData);
}

fclose($file);

echo "Data saved in a csv file with success. Data location: http://localhost/scraping/" . $DATA_FILE_LOCATION;

$html_content->clear();
