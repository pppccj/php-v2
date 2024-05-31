<?php

$router->addGet('/','HomeController@index');
// $router->addGet('/listings','controllers/listings/index.php');
// $router->addGet('/listings/create','controllers/listings/create.php');
// $router->addGet('/listing','controllers/listings/show.php');

$router->addGet('/listings','ListingController@index');
$router->addGet('/listings/search','ListingController@search');
$router->addGet('/listings/create','ListingController@create', ['auth']);
$router->addGet('/listings/{id}','ListingController@show');
$router->addGet('/listings/edit/{id}','ListingController@edit', ['auth']);
$router->addPost('/listings','ListingController@store', ['auth']);
$router->addPut('/listings/{id}','ListingController@update', ['auth']);
$router->addDelete('/listings/{id}','ListingController@destroy', ['auth']);
$router->addGet('/auth/register','UserController@create', ['guest']);
$router->addGet('/auth/login','UserController@login', ['guest']);
$router->addPost('/auth/register','UserController@store', ['guest']);
$router->addPost('/auth/logout','UserController@logout', ['auth']);
$router->addPost('/auth/login','UserController@authenticate', ['guest']);
