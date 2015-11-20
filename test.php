<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);


require_once 'vendor/autoload.php';

$ccb = new \CCB\Api('username', 'password', 'https://yourdomain.ccbchurch.com/api.php');


# Example 1 -- How to fetch and loop over api data


// fetch transaction detail type list
$response = $ccb->fetch('transaction_detail_type_list');

// loop over results (if any)
if ($response['transaction_detail_types']->attr('count') > 0) {
    foreach ($response['transaction_detail_type'] as $element) {
        $item = pq($element);
        var_dump($item['name']->text());
    }
}


# Example 2 -- How to build and post xml body data to api

// get model from local file /ccb/templates/import_online_gifts.xml
// you could also skip this and build your xml string directly
// add your own templates by saving them from the api docs to the template directory
$model = $ccb->fromTemplate('srv_endpoint_string');
$model['element_name']->text('value');
// ...
$response = $ccb->fetch('srv_endpoint_string', $model, 'POST');
var_dump($response);

# Example 3 -- How to build and send GET data to api

$params = array(
    // required
    'coa_category_id' => 50,
    'individual_id'   => 0,
    'amount'          => number_format(rand(100, 300), 2),
    // optional
    'merchant_transaction_id'     => uniqid('OG'), // max 50
    'merchant_authorization_code' => 'AP', // max 10
    'merchant_notes'              => 'Test Donation Api', // max 100
    'merchant_process_date'       => date('Y-m-d'),
    'first_name'                  => 'John',
    'last_name'                   => 'Doe',
    'street_address'              => '12th Ave North', // max 150
    'city'                        => 'Myrtle Beach', // max 30
    'state'                       => 'SC', // max 3
    'zip'                         => '29579', // max 10
    'email'                       => 'john.doe@example.com',
    'campus_id'                   => 1,
    'payment_method'              => 'API - CCard',
    'payment_method_type'         => 'VISA', // enum (Other, MC, VISA, AMEX, DISC
    'transaction_date'            => date('Y-m-d'),
);

try {
    $response = $ccb->fetch('online_giving_insert_gift', $params);
    var_dump($response['gift_id']->text());
} catch (\CCB\Exception $e) {
    var_dump($e->getMessage(), $e->getCode(), $e->getXml());
}

