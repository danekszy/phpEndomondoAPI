<?php
require_once 'config.inc.php';
require_once 'endomondo.inc.php';
$endo = new Endomondo($config);
// print_r (count($endo->getActivities(null, 15))); //''
// echo" + ";
// print_r (count($endo->getWorkoutList(null, 24))); 
// foreach ($endo->getActivities(null, 31)->data as $value) {
// 	echo $value->id.": ".$value->order_time.": ".$value->from->name." - ".$value->message->text."\n";
// }
echo print_r($endo->getWorkoutDetails());//));
//$fr=$endo->getFriendsSummary();
//print_r($fr);
?>
