<?php
include("conn.php");
session_start();
if (empty($_SESSION['email']) || empty($_SESSION['id'])) {
  echo "Error: Someting is wrong";
}
else{
	$item_id=$_POST['item_id'];
	$current_rank=$_POST['current_rank'];
	$user_id = $_SESSION['id'];
	
	if($item_id != ''){
	    $votecheck_sql = "UPDATE camp_users_vote SET closed='1', close_rank='$current_rank' where user_id=".$user_id." and item_id=".$item_id;
    	if (mysqli_query($conn, $votecheck_sql)) {
          echo json_encode(array("statusCode"=>200, "itemid"=>$item_id, "message"=>"Closed successfull"));
        } else {
          echo json_encode(array("statusCode"=>503,"message"=>"Error: " . $sql . "<br>" . mysqli_error($conn)));
        }
	    
	}
	
	
}