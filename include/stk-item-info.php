<?php
include("conn.php");
session_start();
if (empty($_SESSION['email']) || empty($_SESSION['id'])) {
  echo "Error: Someting is wrong";
} else{
    $item_id=$_POST['item_id'];
    $camp_url = 'http://www.demandmarketapp.com/DemandAPI/get_campaign_info';
    $camp_json = file_get_contents($camp_url);
    $camp_details = json_decode($camp_json);
    $camp_count=1;
    foreach($camp_details->results as $camp_detail){
      $camp_detail->item_id;
      if($camp_detail->item_id == $item_id){
          $st_code = 200;
          $msg = '<img src="'.$camp_detail->image.'" /><div class="item-desc">'.$camp_detail->title.'</div>';
          break;
      }
      else {
          $st_code = 404;
          $msg = 'Not found';
      }
    }
    echo json_encode(array("statusCode"=>$st_code,"message"=>$msg));
}