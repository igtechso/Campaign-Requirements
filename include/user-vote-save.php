<?php
include("conn.php");
session_start();
if (empty($_SESSION['email']) || empty($_SESSION['id'])) {
  echo "Error: Someting is wrong";
}
else{
    $camp_id=$_POST['camp_id'];
	$item_id=$_POST['item_id'];
	$vote_type=$_POST['vote_type'];
	$current_rank=$_POST['current_rank'];
	$user_email = $_SESSION['email'];
	$user_id = $_SESSION['id'];
	
	if($camp_id != '' && $item_id != '' && $vote_type != ''){
	    $votecheck_sql = "SELECT * FROM camp_users_vote where user_id=".$user_id." and item_id=".$item_id;
    	$votecheck_result = mysqli_query($conn, $votecheck_sql);
    	if (mysqli_num_rows($votecheck_result) > 0) {
    	    echo json_encode(array("statusCode"=>200,"message"=>"Vote already submitted"));
    	}
    	else{
    	    //echo "done";
    	    if($vote_type == "voteup"){
    	        $user_vote = 1;
    	    }else if($vote_type == "votedown") {
    	        $user_vote = 0;
    	    }
    	    
    	    $addvote_sql = "INSERT INTO camp_users_vote (user_id, user_email, camp_id, item_id, user_vote, open_rank, open_profit, close_rank, closed) VALUES ('$user_id', '$user_email', '$camp_id', '$item_id', '$user_vote', '$current_rank', '', '', '')";
            
            if (mysqli_query($conn, $addvote_sql)) {
              $camp_url = 'http://www.demandmarketapp.com/DemandAPI/get_campaign_info';
              $camp_json = file_get_contents($camp_url);
              $camp_details = json_decode($camp_json);

              $camp_details_new_array = array();
              $camp_count=0;
              foreach ($camp_details->results as $key => $camp_detail) {
                $camp_details_new_array[$camp_count]['campaign_id'] = $camp_detail->campaign_id;
                $camp_details_new_array[$camp_count]['created_on'] = $camp_detail->created_on;
                $camp_details_new_array[$camp_count]['currently_have'] = $camp_detail->currently_have;
                $camp_details_new_array[$camp_count]['description'] = $camp_detail->description;
                $camp_details_new_array[$camp_count]['image'] = $camp_detail->image;
                $camp_details_new_array[$camp_count]['item_id'] = $camp_detail->item_id;
                $camp_details_new_array[$camp_count]['price'] = $camp_detail->price;
                $camp_details_new_array[$camp_count]['price_reduction'] = $camp_detail->price_reduction;
                $camp_details_new_array[$camp_count]['title'] = $camp_detail->title;
                $camp_details_new_array[$camp_count]['user_id'] = $camp_detail->user_id;

                $tot_vt_sql = "SELECT * FROM camp_users_vote where item_id=".$camp_detail->item_id;
                $tot_vt_result = mysqli_query($conn, $tot_vt_sql);
                $tot_vt_count = mysqli_num_rows($tot_vt_result);
                $tot_up_count = 0;
                $tot_down_count = 0;
                if ($tot_vt_count > 0) {
                    while($tot_vt_row = mysqli_fetch_assoc($tot_vt_result)) {
                      if ($tot_vt_row['user_vote'] == 1) {
                        $tot_up_count = $tot_up_count+1;
                      } else if ($tot_vt_row['user_vote'] == 0) {
                        $tot_down_count = $tot_down_count+1;
                      }                                
                    }
                    $camp_details_new_array[$camp_count]['current_rank'] = round(($tot_up_count * 100) / $tot_vt_count, 2);
                }
                else {
                  $camp_details_new_array[$camp_count]['current_rank'] = -1;
                }

                $camp_count++;
                if ($camp_count == 9) {
                  break;
                }

              }
              $current_rank_column = array_column($camp_details_new_array, 'current_rank');
              array_multisort($current_rank_column, SORT_DESC, $camp_details_new_array);
              $camp_count = 1;
              foreach($camp_details_new_array as $camp_detail) {
                  $tot_vt_sql2 = "SELECT * FROM camp_users_vote where item_id='".$camp_detail['item_id']."' and user_id='".$_SESSION['id']."' ";
                  $tot_vt_result2 = mysqli_query($conn, $tot_vt_sql2);
                  $tot_vt_row2 = mysqli_fetch_assoc($tot_vt_result2);
                  if ($_SESSION['id'] == $tot_vt_row2['user_id']) {
                    if ($tot_vt_row2['open_rank'] > $camp_count) {
                      $profit = $tot_vt_row2['open_rank'] - $camp_count;
                    }
                    else {
                      $profit = $camp_count - $tot_vt_row2['open_rank'];
                    }
                    $upd_opn_prof_sql = "UPDATE camp_users_vote SET open_profit='$profit' where user_id=".$_SESSION['id']." and item_id=".$camp_detail['item_id'];
                    mysqli_query($conn, $upd_opn_prof_sql);
                    break;
                  }
                $camp_count++;
              }
                  
              echo json_encode(array("statusCode"=>200,"message"=>"Vote submitted"));
            } else {
              echo json_encode(array("statusCode"=>503,"message"=>"Error: " . $sql . "<br>" . mysqli_error($conn)));
            }
    	    
    	}
	    
	}
	
	
}