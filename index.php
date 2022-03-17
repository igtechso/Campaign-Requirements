<?php
include("common/header.php"); 
include("include/conn.php"); 
?>
<?php
session_start();
if (empty($_SESSION['email']) || empty($_SESSION['id'])) {
  header("location: login.php");
  die();
}
?>
<div class="main-content">
  <div class="home-page">
    <div class="compaign-box">


      <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
          <a class="nav-link active" data-bs-toggle="tab" href="#stlist">Show Votes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#uservote">User Votes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="tab" href="#total">Total</a>
        </li>
      </ul>

      <!-- Tab panes -->
      <div class="tab-content">
        <div id="stlist" class="container tab-pane active"><br>
          <h3>Votes</h3>
            <table class="table-style1" cellspacing="0">
                <thead>
                    <tr>
                        <th>Market</th>
                        <th>Up / Down Votes</th>
                        <th>Current Rank</th>
                        <th>Your Profit</th>
                        <th>Up Vote</th>
                        <th>Down Vote</th>
                        <th>Exit</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
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
                  ?>
                    <tr campaign-id="<?php echo $camp_detail['campaign_id']; ?>" item-id="<?php echo $camp_detail['item_id']; ?>" current-rank="<?php echo ($camp_detail['current_rank'] != -1) ? $camp_count : 0 ?>">
                        <td><?php echo $camp_detail['title']; ?> <a href="javascript:void(0)" class="stk-info"><i class="fa fa-info-circle" aria-hidden="true"></i></a></td>
                        <td class="vote-pers">
                          <?php
                          $tot_vt_sql = "SELECT * FROM camp_users_vote where item_id=".$camp_detail['item_id'];
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
                              echo "<span class='voteup-pers'>".round(($tot_up_count * 100) / $tot_vt_count, 2)."%</span> / <span class='votedown-pers'>".round(($tot_down_count * 100) / $tot_vt_count, 2)."%</span>";
                          }
                          else {
                            echo 'N/A';
                          }
                          ?>
                        </td>
                        <td>
                          <?php
                          if ($camp_detail['current_rank'] != -1) {
                            echo $camp_count;
                          } else {
                            echo 'N/A';
                          }                          
                          ?>                            
                        </td>
                        <td>
                          <?php
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
                            echo "$-".$profit;
                          }
                          ?>
                        </td>
                        <td><span class="vote-up stk-vote" voteval="voteup"><i class="fa fa-long-arrow-up" aria-hidden="true"></i></span></td>
                        <td><span class="vote-down stk-vote" voteval="votedown"><i class="fa fa-long-arrow-down" aria-hidden="true"></i></span></td>
                        <td>
                          <?php
                          if ($_SESSION['id'] == $tot_vt_row2['user_id'] && $tot_vt_row2['closed'] == 0)
                          {
                          ?>
                            <span class="vote-close"><a class="stk-close" href="javascript:void(0);"><i class="fa fa-times-circle-o" aria-hidden="true"></i></a></span>
                          <?php
                          }
                          else
                          {
                          ?>
                            <span class="vote-close"><i class="fa fa-ban" aria-hidden="true"></i></span>
                          <?php
                          }
                          ?>                          
                        </td>
                    </tr>
                  <?php
                    $camp_count++;
                  }
                  ?>                   
                </tbody>
            </table>
        </div>
        <div id="uservote" class="container tab-pane fade"><br>
          <h3>Your Votes</h3>
          <div class="tab-content-inner">
            <table class="table-style1" cellspacing="0">
              <thead>
                    <tr>
                        <th>Market</th>
                        <th>Up / Down Votes</th>
                        <th>Total Up / Down Votes</th>                        
                        <th>Your Profit</th>
                        <th>Current Rank</th>
                        <th>Average Open Rank</th>
                        <th>Exit</th>
                    </tr>
                </thead>
                <tbody>
                  <?php
                  $camp_count = 1;
                  $user_total_profit = 0;
                  foreach($camp_details_new_array as $camp_detail) {
                    $user_vt_sql = "SELECT * FROM camp_users_vote where item_id=".$camp_detail['item_id']." and user_id=".$_SESSION['id'];
                    $user_vt_result = mysqli_query($conn, $user_vt_sql);
                    $user_vt_count = mysqli_num_rows($user_vt_result);
                    if ($user_vt_count > 0) {
                    ?>
                      <tr item-id="<?php echo $camp_detail['item_id']; ?>" current-rank="<?php echo ($camp_detail['current_rank'] != -1) ? $camp_count : 0 ?>">
                        <td><?php echo $camp_detail['title']; ?> <a href="javascript:void(0)" class="stk-info"><i class="fa fa-info-circle" aria-hidden="true"></i></a></td>
                        <td class="vote-pers">
                          <?php
                          $tot_vt_sql = "SELECT * FROM camp_users_vote where item_id=".$camp_detail['item_id'];
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
                              echo "<span class='voteup-pers'>".round(($tot_up_count * 100) / $tot_vt_count, 2)."%</span> / <span class='votedown-pers'>".round(($tot_down_count * 100) / $tot_vt_count, 2)."%</span>";
                          }
                          else {
                            echo 'N/A';
                          }
                          ?>
                        </td>
                        <td class="vote-pers">
                          <?php
                          echo "<span class='voteup-pers'>".$tot_up_count."</span> / <span class='votedown-pers'>".$tot_down_count."</span>";
                          ?>
                        </td>
                        <td>
                          <?php
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
                            echo "$-".$profit;
                            $user_total_profit = $user_total_profit+$profit;
                          }
                          ?>
                        </td>
                        <td>
                          <?php
                          if ($camp_detail['current_rank'] != -1) {
                            echo $camp_count;
                          } else {
                            echo 'N/A';
                          }                          
                          ?>                            
                        </td>
                        <td>
                          <?php
                          $opn_rnk_sql = "SELECT * FROM camp_users_vote where item_id=".$camp_detail['item_id'];
                          $opn_rnk_result = mysqli_query($conn, $opn_rnk_sql);
                          $opn_rnk_tl_cnt = mysqli_num_rows($opn_rnk_result);
                          if ($opn_rnk_tl_cnt > 0) {
                            $opn_rank_count = 0;
                            while($opn_rnk_row = mysqli_fetch_assoc($opn_rnk_result)) {
                              $opn_rank_count = $opn_rank_count + $opn_rnk_row['open_rank'];
                            }
                            echo round($opn_rank_count/$opn_rnk_tl_cnt, 2);
                          }
                          ?>
                        </td>
                        <td>
                          <?php
                          if ($_SESSION['id'] == $tot_vt_row2['user_id'] && $tot_vt_row2['closed'] == 0)
                          {
                          ?>
                            <span class="vote-close"><a class="stk-close" href="javascript:void(0);"><i class="fa fa-times-circle-o" aria-hidden="true"></i></a></span>
                          <?php
                          }
                          else
                          {
                          ?>
                            <span class="vote-close"><i class="fa fa-ban" aria-hidden="true"></i></span>
                          <?php
                          }
                          ?>                          
                        </td>
                      </tr>                      
                    <?php
                    }
                    $camp_count++;
                  }
                  ?>
                </tbody>
            </table>
          </div>
        </div>
        <div id="total" class="container tab-pane fade"><br>
          <h3>Totals</h3>
          <div class="tab-content-inner">
            <table class="table-style1 table-horiz" cellspacing="0">
              <tbody>
                <tr>
                  <th>Total Votes</th>
                  <td>
                    <?php
                    $usr_vt_sql = "SELECT * FROM camp_users_vote where user_id=".$_SESSION['id'];
                    $usr_vt_result = mysqli_query($conn, $usr_vt_sql);
                    echo $usr_vt_cnt = mysqli_num_rows($usr_vt_result);
                    ?>
                  </td>
                </tr>
                <tr>
                  <th>Total Votes Percentile</th>
                  <td>
                    <?php
                    $user_ids_arr = array();
                    $usr_ttl_sql = "SELECT * FROM camp_register";
                    $usr_ttl_result = mysqli_query($conn, $usr_ttl_sql);
                    $usr_ttl_cnt = mysqli_num_rows($usr_ttl_result);
                    while($usr_ttl_row = mysqli_fetch_assoc($usr_ttl_result)) {
                      //if ($_SESSION['id'] != $usr_ttl_row['id']) {
                        $user_ids_arr[] = $usr_ttl_row['id'];
                      //}                      
                    }
                    $less_user_votes = array();
                    foreach($user_ids_arr as $user_ids_val){
                        $s_usr_vt_sql = "SELECT * FROM camp_users_vote where user_id='$user_ids_val'";
                        $s_usr_vt_result = mysqli_query($conn, $s_usr_vt_sql);
                        $s_usr_vt_cnt = mysqli_num_rows($s_usr_vt_result);
                        if ($s_usr_vt_cnt <= $usr_vt_cnt) {
                          $less_user_votes[] = $s_usr_vt_cnt;
                        }               
                    }
                    $less_user_count = count($less_user_votes);
                    $all_user_count = count($user_ids_arr);
                    echo $ttl_vt_prtl = ($less_user_count/$all_user_count)*100;
                    ?>
                  </td>
                </tr>
                <tr>
                  <th>Total Profit</th>
                  <td>
                    <?php
                    echo "$".$user_total_profit;
                    ?>
                  </td>
                </tr>
                <tr>
                  <th>Equity Percentile</th>
                  <td>
                    <?php
                    $user_eqt_arr = array();
                    $user_eqt_arr_count = 0;
                    foreach($user_ids_arr as $user_ids_val){
                        $al_cls_vt_sql = "SELECT * FROM camp_users_vote where user_id='$user_ids_val' and closed='1' ";
                        $al_cls_vt_result = mysqli_query($conn, $al_cls_vt_sql);
                        $al_cls_vt_cnt = mysqli_num_rows($al_cls_vt_result);
                        $user_eqt = 0;                        
                        while($al_cls_vt_row = mysqli_fetch_assoc($al_cls_vt_result)) {
                          $opn_prof = $al_cls_vt_row['open_profit'];
                          if ($al_cls_vt_row['open_rank'] > $al_cls_vt_row['close_rank']) {
                            $cls_profit = $al_cls_vt_row['open_rank'] - $al_cls_vt_row['close_rank'];
                          }
                          else {
                            $cls_profit = $al_cls_vt_row['close_rank'] - $al_cls_vt_row['open_rank'];
                          }
                          $ea_user_eqt = $opn_prof+$cls_profit;
                          $user_eqt = $user_eqt+$ea_user_eqt;

                        }

                      if ($user_ids_val == $_SESSION['id']) {
                        $lg_user_eqt = $user_eqt;                            
                      }

                      $user_eqt_arr[$user_eqt_arr_count]['userid'] = $user_ids_val;
                      $user_eqt_arr[$user_eqt_arr_count]['usereqt'] = $user_eqt;
                      $user_eqt_arr_count++;
                    }
                    
                    $user_ls_eqt_arr = array();
                    foreach($user_eqt_arr as $user_eqt_val){
                      if ( $user_eqt_val['usereqt'] <= $lg_user_eqt) {
                        $user_ls_eqt_arr[] = $user_eqt_val['usereqt'];
                      }
                    }
                    $user_ls_eqt_count = count($user_ls_eqt_arr);
                    $user_eqt_count = count($user_eqt_arr);
                    echo $ttl_user_eqt_prtl = ($user_ls_eqt_count/$user_eqt_count)*100;
                    ?>
                  </td>
                </tr>
                <tr>
                  <th>Your Current (%) share of budget</th>
                  <td>
                    <?php echo $overall_percentile = $ttl_vt_prtl*$ttl_user_eqt_prtl;?>
                  </td>
                </tr>
                <tr>
                  <th>Your Current ($) share of budget</th>
                  <td>
                    <?php
                    $ttl_share_buget = $overall_percentile*1000;
                    echo $ttl_share_buget = $ttl_share_buget."$";
                    ?>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>    
</div>

<?php include("common/footer.php"); ?>