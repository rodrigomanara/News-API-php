<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once './vendor/autoload.php';

$api = new \NewsApi\Api( array('q' => 'Reino Unido' , 'language'=> 'pt' , 'apiKey' => ''));
$data = $api->getData();


var_dump($data);