var massmove_modal, from_levelid, from_levelname, to_levelid, to_levelname, action, processing;
jQuery(function($){
	massmove_modal = new wlm3_modal( '#massmove-modal', massmove_members );
	$('.moveadd-members').click(show_massmove_modal);
	$('.move-to-level').change(function(){ $(this).parent().removeClass('has-error')});

	$("#" +massmove_modal.data.id).on('hide.bs.modal', function(e) {
		if ( processing ) {
			e.preventDefault();
		} else {
			if ( $("#" +massmove_modal.data.id).find(".cancel-button").html() == wlm.translate( 'Close' ) ) {
				// window.parent.location.reload(true);
				$(this).reload_screen();
			}
		}
	});
});

var show_massmove_modal = function() {
	from_levelid   = $(this).attr("data-levelid");
	from_levelname = $(this).closest("tr").find(".level-name").html();
	to_levelid   = $(this).closest("tr").find(".move-to-level").val();
	to_levelname = $(this).closest("tr").find(".move-to-level option:selected").text();
	action = $(this).attr("data-action");
	$(".move-to-level").parent().removeClass("has-error");
	if ( !to_levelid || to_levelid == "" ) {
		msg = wlm.translate( 'Please select the membership level you want to $$1 the members of $$2 ', [action, from_levelname]);
		$(".wlm-message-holder").show_message({message: msg, type: "danger" });
		$(this).closest("tr").find(".move-to-level").parent().addClass("has-error");
		return false;
	}
	if ( action == "move" ) {
		op_msg = wlm.translate( 'Please do not leave this page while we are moving your members.' );
	} else {
		op_msg = wlm.translate( 'Please do not leave this page while we are adding your members.' );
	}

	$("#" +massmove_modal.data.id).find("input[name='from_lvlid']").val(from_levelid);
	$("#" +massmove_modal.data.id).find("input[name='to_lvlid']").val(to_levelid);
	$("#" +massmove_modal.data.id).find("input[name='operation']").val(action);
	$("#" +massmove_modal.data.id).find(".message").html(wlm.translate( 'You are about to <strong>$$1</strong> the members of \'<em>$$2</em>\' to \'<em>$$3</em>\'.', [ action.toUpperCase(), from_levelname, to_levelname ] ) );
	$("#" +massmove_modal.data.id).find(".message2").html(wlm.translate( 'Do you want to proceed?' ));
	$("#" +massmove_modal.data.id).find(".progress-holder").hide();
	$("#" +massmove_modal.data.id).find(".progress-holder .progress-bar").css("width", "0%");
	$("#" +massmove_modal.data.id).find(".operation-warning").show();
	$("#" +massmove_modal.data.id).find(".operation-warning").html(op_msg);
	$("#" +massmove_modal.data.id).find(".close").show();
	$("#" +massmove_modal.data.id).find(".cancel-button").show();
	$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate ( 'Cancel' ));
	$("#" +massmove_modal.data.id).find(".save-button").show();
	massmove_modal.open();

}

var massmove_members = function() {
	var msg = "";
	var op_msg = "";
	if ( action == "move" ) {
		op_msg = wlm.translate( 'Please do not leave this page while we are moving your members.' );
		msg = wlm.translate( 'Moving members from \'<em>$$1</em>\' to \'<em>$$2</em>\'.', [from_levelname, to_levelname] );
	} else {
		msg = wlm.translate( 'Adding members of \'<em>$$1</em>\' to \'<em>$$2</em>\'.', [from_levelname, to_levelname] );
		op_msg = wlm.translate( 'Please do not leave this page while we are adding your members.' );
	}
	msg += ' ' + wlm.translate( 'Please wait ...' );
	$("#" +massmove_modal.data.id).find(".message").html(msg);
	$("#" +massmove_modal.data.id).find(".message2").html("");
	$("#" +massmove_modal.data.id).find(".operation-warning").html(op_msg);

	$("#" +massmove_modal.data.id).find(".progress-holder").show();
	$("#" +massmove_modal.data.id).find(".progress-holder .progress-bar").css("width", "0%");
	$("#" +massmove_modal.data.id).find(".progress-holder .operation-loading").show();

	$("#" +massmove_modal.data.id).find(".close").hide();
	$("#" +massmove_modal.data.id).find(".cancel-button").hide();
	$("#" +massmove_modal.data.id).find(".save-button").hide();
	var settings_data = {
		action : "admin_actions",
		WishListMemberAction : "get_level_memberids",
		lvlid : from_levelid
	};

	var x = $(this).save_settings({
		data: settings_data,
	    on_init: function( $me, $data) {
	    	$("#" +massmove_modal.data.id).find(".progress-holder .operation-msg").html(wlm.translate( 'Retrieving members of $$1', [from_levelname] ) );
	    },
	    on_success: function( $me, $result) {
	    	if ( $result.ids && $result.ids.length > 0 ) {
	    		$("#" +massmove_modal.data.id).find(".progress-holder .progress-bar").attr("aria-valuemax", $result.ids.length );
				$("#" +massmove_modal.data.id).find(".progress-holder .progress-bar").attr("aria-valuenow", 0 );
				do_moveadd( $result.ids, $result.ids.length );
	    	} else {
				$("#" +massmove_modal.data.id).find(".message").html(from_levelname +" has no members.");
				$("#" +massmove_modal.data.id).find(".progress-holder").hide();
				$("#" +massmove_modal.data.id).find(".close").show();
				$("#" +massmove_modal.data.id).find(".cancel-button").show();
				$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate( 'Close' ));
				processing = false;
				console.log($result);
	    	}
	    },
	    on_fail: function( $me, $data) {
			$("#" +massmove_modal.data.id).find(".message").html(wlm.translate( 'Unable to retrieve members of $$1', [from_levelname] ) );
			$("#" +massmove_modal.data.id).find(".progress-holder").hide();
			$("#" +massmove_modal.data.id).find(".close").show();
			$("#" +massmove_modal.data.id).find(".cancel-button").show();
			$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate( 'Close' ));
			processing = false;
			console.log($data);
	    },
	    on_error: function( $me, $error_fields) {
			$("#" +massmove_modal.data.id).find(".message").html(wlm.translate( 'An error occured while retrieving members of $$1', [from_levelname] ) );
			$("#" +massmove_modal.data.id).find(".progress-holder").hide();
			$("#" +massmove_modal.data.id).find(".close").show();
			$("#" +massmove_modal.data.id).find(".cancel-button").show();
			$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate( 'Close' ));
			processing = false;
			console.log($data);
	    }
	});
	var do_moveadd = function( ids, total ) {
		processing = true;
		if ( ids.length ) {
			var cnt = 5;
			var ids_holder = [];
			var do_delay = total <= 5; //do delay on first
			while ( ids.length && cnt  ) {
				ids_holder.push( ids.pop() );
				cnt--;
			}
			var count = total - ids.length;
			var settings_data = {
				action : "admin_actions",
				WishListMemberAction : "massmove_members",
				from_levelid : from_levelid,
				to_levelid : to_levelid,
				operation : action,
				ids : ids_holder,
			};
			if ( do_delay ) settings_data['wlmdelay'] = 2;

			var x = $(this).save_settings({
				data: settings_data,
			    on_success: function( $me, $result) {
			    	if ( $result.success ) {
						$("#" +massmove_modal.data.id).find(".progress-holder .operation-msg").html(wlm.translate( 'Processing $$1 of $$2', [ count, total ] ) );
						$("#" +massmove_modal.data.id).find(".progress-holder .progress-bar").attr("aria-valuenow", count );
						$("#" +massmove_modal.data.id).find(".progress-holder .progress-bar").css("width", ( ( count / total ) * 100 ) + "%");
			    		do_moveadd( ids, total );
			    	} else {
						$("#" +massmove_modal.data.id).find(".progress-holder .operation-msg").html($result.msg);
						$("#" +massmove_modal.data.id).find(".operation-warning").hide();
						$("#" +massmove_modal.data.id).find(".close").show();
						$("#" +massmove_modal.data.id).find(".cancel-button").show();
						$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate( 'Close' ));
						$("#" +massmove_modal.data.id).find(".progress-holder .operation-loading").hide();
						processing = false;
						console.log($result);
			    	}
			    },
			    on_fail: function( $me, $data) {
					$("#" +massmove_modal.data.id).find(".progress-holder .operation-msg").html(wlm.translate( 'An error occured!' ) );
					$("#" +massmove_modal.data.id).find(".operation-warning").hide();
					$("#" +massmove_modal.data.id).find(".close").show();
					$("#" +massmove_modal.data.id).find(".cancel-button").show();
					$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate( 'Close' ));
					$("#" +massmove_modal.data.id).find(".progress-holder .operation-loading").hide();
					processing = false;
					console.log($data);
			    },
			    on_error: function( $me, $error_fields) {
					$("#" +massmove_modal.data.id).find(".progress-holder .operation-msg").html(wlm.translate( 'An error occured!' ) );
					$("#" +massmove_modal.data.id).find(".operation-warning").hide();
					$("#" +massmove_modal.data.id).find(".close").show();
					$("#" +massmove_modal.data.id).find(".cancel-button").show();
					$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate( 'Close' ));
					$("#" +massmove_modal.data.id).find(".progress-holder .operation-loading").hide();
					processing = false;
			    }
			});
		} else {
			var action_string = action == 'move' ? wlm.translate( 'Moved' ) : wlm.translate ( 'Added' );
			var msg = wlm.translate( '$$1 Members of \'<em>$$2</em>\' haved been $$3 to \'<em>$$4</em>\'.', [ total, from_levelname, action_string, to_levelname] );
			$("#" +massmove_modal.data.id).find(".message").html(msg);
			$("#" +massmove_modal.data.id).find(".progress-holder .operation-msg").html(wlm.translate ( 'Done!' ) );
			$("#" +massmove_modal.data.id).find(".progress-holder .operation-loading").hide();
			$("#" +massmove_modal.data.id).find(".operation-warning").hide();
			$("#" +massmove_modal.data.id).find(".close").show();
			$("#" +massmove_modal.data.id).find(".cancel-button").show();
			$("#" +massmove_modal.data.id).find(".cancel-button").html(wlm.translate( 'Close' ));
			processing = false;
		}
	}
}