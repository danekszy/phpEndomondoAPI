<?php
require_once 'config.inc.php';
require_once 'endomondo.inc.php';
$endo = new Endomondo($config);
print_r ($endo->getMyWorkouts());

//echo print_r($endo->getWorkoutDetails(233188289));
?>