$(document).ready(function(){
	
	$('body').on('click', '.stk-vote', function() {
	    $('.camp-loader').addClass('show');
	    var camp_id = $(this).parent().parent().attr("campaign-id");
	    var item_id = $(this).parent().parent().attr("item-id");
	    var current_rank = $(this).parent().parent().attr("current-rank");
	    var vote_type = $(this).attr("voteval");
	    $.ajax({
            method:"POST",
            url: "include/user-vote-save.php",
            data: {
    			camp_id: camp_id,
    			item_id: item_id,
    			current_rank: current_rank,
    			vote_type: vote_type			
    		},
    		cache: false,
            success: function(data){
                var dataResult = JSON.parse(data);
                if(dataResult.statusCode==200){
                    $('.camp-loader').removeClass('show');
                    $('#resModal .res-msg').html(dataResult.message);
                    $('#resModal').modal('show');
                }
                else{
                    $('.camp-loader').removeClass('show');
                    $('#resModal .res-msg').html(dataResult.message);
                    $('#resModal').modal('show');
                }
        }});
	});
	
	
	$('body').on('click', '.stk-info', function() {
	    var item_id = $(this).parent().parent().attr("item-id");
	    $('.camp-loader').addClass('show');
	    $.ajax({
            method:"POST",
            url: "include/stk-item-info.php",
            data: {
    			item_id: item_id			
    		},
    		cache: false,
            success: function(data){
                console.log(data);
                var dataResult = JSON.parse(data);
                $('.camp-loader').removeClass('show');
                $('#stkinfoModal .stkinfo-details').html(dataResult.message);
                $('#stkinfoModal').modal('show');
        }});
	});
	
	
	$('body').on('click', '.stk-close', function() {
	    var item_id = $(this).parent().parent().parent().attr("item-id");
	    var current_rank = $(this).parent().parent().parent().attr("current-rank");
	    $.ajax({
            method:"POST",
            url: "include/stk-close.php",
            data: {
    			item_id: item_id,
    			current_rank: current_rank
    		},
    		cache: false,
            success: function(data){
                console.log(data);
                var dataResult = JSON.parse(data);
                $('#stkinfoModal .stkinfo-details').html(dataResult.message);
                $('table tr[item-id="'+dataResult.itemid+'"] .vote-close').html('<i class="fa fa-ban" aria-hidden="true"></i>');
                $('#stkinfoModal').modal('show');
        }});
	});
	

});