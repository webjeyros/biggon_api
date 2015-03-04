# biggon_api
php class wrapper for biggon.ru api
# example
<?php
require 'biggon.php';
$biggon = new Biggon("YOU_KEY_HERE","json");
var_dump($biggon->items_get(1154,1,1,1,"RU"));
