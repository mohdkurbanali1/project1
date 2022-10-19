var wlm3sl;
function wlm3_screen_levels() {
	this.init();
}
wlm3_screen_levels.prototype = {
	loading_form: false,
	save_queue: [],
	is_saving: false,
	expire_hide_show_notif_button: function() {
		if($('[name=expire_option]').val() == '0') {
			$('.expire_notification').hide();
		} else {
			$('.expire_notification').show();
		}
	},
	apply_do_confirm: function() {
		$( '#levels-list .-del-btn' ).do_confirm( { confirm_message : wlm.translate( 'Delete this Level?' ), yes_button : wlm.translate( 'Delete' ) } ).on( 'yes.do_confirm', { that : this }, this._delete );
	},
	modal_close: function(event) {
		var modal = $(event.currentTarget);
		var lid = $('#levels-create input[name=id]').val();
		modal.find(':input[name]:not(.is-toggle-switch)').each(function() {
			$(this).val([wpm_levels[lid][$(this).attr('name')]]);
			$(this).change();
		});
	},
	list_levels: function() {
		var data = {
			levels: Object.values(wpm_levels).slice( pagination.from - 1, pagination.per_page + pagination.from - 1 ),
			link: members_link,
			stats: level_stats,
		};
		var tmpl = _.template($('script#levels-list-template').html(), {
			variable: 'data'
		});
		var html = tmpl(data);
		$('#levels-list-table tbody').replaceWith('<tbody/>');
		$('#levels-list-table tbody').append(html.trim());
		$('#levels-list-table tbody').sort('tr', 'data-sort', true);
		$('a.-no-delete').tooltip({trigger : 'click'}).on('mouseout', function() {
			$(this).tooltip('hide');
		});
		this.apply_do_confirm();
		$('#levels-list-table').removeClass('d-none');
		$.post(WLM3VARS.ajaxurl, { action : 'wlm3_get_level_stats' }, function (stats) {
			$.each(stats, function(index, stats) {
				$.each(stats, function(level, count) {
					$('tr[data-id="' + level + '"] .' + index + ' a').text( parseInt( count ).toLocaleString( 'us' ) );
				});
			});
			level_stats = stats;
		});
	},
	show_form: function(level, save_only) {
		var that = this;
		this.loading_form = true;
		$('#levels-list').hide();
		var f = $('form#level-form');
		f[0].reset();

		var sender_name = '<input type="text" class="form-control -sender-default d-none" disabled value="' + wlm_sender_default.name + '">';
		var sender_email = '<input type="text" class="form-control -sender-default d-none" disabled value="' + wlm_sender_default.email + '">';
		$('.level-sender-info').each(function() {
			var inputs = $(this).find(':input.form-control');
			$(inputs[0]).after(sender_name);
			$(inputs[1]).after(sender_email);
		});

		$.each(level, function(n, v) {
			if (v == null) return; // skip null values
			var i = f.find(':input[name=' + n + ']');
			if(i.length < 1) return true;

			if(i.hasClass('richtext')) {
				if(!v.match(/<.+?>/g)) {
					v = v.replace(/\n\n/g, '<p>').replace(/\n/g, '<br>');
				}
			}
			i.val(Array.isArray(v) ? v : [v]);

			// we selectively trigger .change for performance
			if(i[0].type.match(/^(select-.+)$/)) {
				i.change();
			}

			if(i.hasClass('-sender-default-toggle')) {
				i.change();
			}

			if(i[0].type == 'radio') {
				i.filter(':checked').data('iamthis', 1);
			}
		});

		// hide "add/remove from level" settings if necessary
		var lc = Object.keys(wpm_levels).length;
		var lcmin = ('newlevel' in level) ? 1 : 2;
		if(lc < lcmin) {
			$('#levels_addremove_from').addClass('d-none');
		} else {
			$('#levels_addremove_from').removeClass('d-none');
		}

		$('#levels-create .wlm_toggle-adjacent').change();

		$('#levels-create .modal :text').addClass('modal-input');
		$('#levels-create .modal textarea').addClass('modal-input');
		$('#levels-create .modal select').addClass('modal-input');

		var xd = moment(level.expire_date, "MMMM D, YYYY");
		// xd = level.expire_date ? (xd.isValid() ? xd : moment(level.expire_date)) : moment().add(7, 'days');
		xd = level.expire_date ? (xd.isValid() ? xd : moment(level.expire_date)) : moment();

		$('#levels-create [name=expire_date]').daterangepicker(
			{
				singleDatePicker: true,
				showCustomRangeLabel: false,
				startDate: xd.format("MMMM D, YYYY"), //making this format fixed will save us trouble since strtotime does not support d/m/y
				locale : {
					format: "MMMM D, YYYY"
				},
				buttonClasses: 'btn -default',
				applyClass: '-condensed -success',
				cancelClass: '-condensed -link'
			}
		);

		$( '#levels-create [name=name]' ).apply_cancel( { show_feedback : false } )
		.on('apply.apply_cancel', {'that' : that}, function( e ) {
			var lname_ok = true;
			var lname = $( this ).val().trim();
			var lid = $( 'input[name=id]' ).val();
			$.each( wpm_levels, function ( id, level ) {
				if( lid == id ) return true;
				if( level.name.trim() == lname ) {
					lname_ok = false;
					return false;
				}
			});

			if(!lname_ok) {
				$( '.wlm-message-holder' ).show_message( { message: wlm.translate( 'Level name is already in use.' ), type: 'danger' } );
				$( 'input[name=name]' ).parent().addClass( 'has-error' );
			} else {
				$( 'input[name=name]' ).apply_cancel( 'show_feedback' );
				e.data.that._save_changes( e );
			}
		} )
		.on('cancel.apply_cancel', function(e) {
			$( this ).val( $( this ).data( 'initial' ) ).change();
		} );

		$( '#levels-create [name=url]' ).click_to_edit().apply_cancel( {
			require_change: false
		} )
		.on('edit.click_to_edit', function() {
			$( this ).apply_cancel( 'show' );
		} )
		.on('apply.apply_cancel', {'that' : that}, function(e) {
			e.data.that._save_changes( e );
			$( this ).click_to_edit( 'close' );
			$( this ).apply_cancel( 'hide' );
		} )
		.on('cancel.apply_cancel', function(e) {
			$( this ).val( $( this ).data( 'initial' ) ).change();
			$( this ).click_to_edit( 'close' );
			$( this ).apply_cancel( 'hide' );
		} );

		$('.page-message-reset-button').do_confirm({placement:'right',yes_classes:'-success'})
		.on('yes.do_confirm', function() {
			var type = $(this).closest('.-holder').data('type') + '_message';
			var target = $('[name="' + type + '"]');
			var editor = tinymce.get(target[0].id);
			editor.setContent(WLM3VARS.level_defaults[type]);
			target.val(WLM3VARS.level_defaults[type]);
			$('#custom-redirects .modal-save-and-continue').click();
		});

		$('.email-reset-button').do_confirm({placement:'right',yes_classes:'-success'})
		.on('yes.do_confirm', function() {
			var type = $(this).data('target');
			var subject = type + '_subject';
			$('[name="' + subject + '"]').val(WLM3VARS.level_defaults[subject]);

			var recipient = type + '_recipient';
			$('[name="' + recipient + '"]').val(WLM3VARS.level_defaults[recipient]);

			var message = type + '_message';
			var target = $('[name="' + message + '"]');
			var editor = tinymce.get(target[0].id);
			editor.setContent(WLM3VARS.level_defaults[message]);
			target.val(WLM3VARS.level_defaults[message]);
			$('#email-notification-settings .modal-save-and-continue').click();
		});


		$('#levels-create').find('input[name=name], input[name=url]').each(function() {
			$(this).data('initial', $(this).val());
			$(this).closest('div').removeClass('has-error');
		});

		$('.expire_settings').data('initial', $('.expire_settings :input').serialize());
		$('.expire_settings [name=expire_option]').change();

		if(!$('#levels-create input[name=name]').val().trim()) {
			$('#levels-create input[name=name]').focus();
		}

		var xid = ('newlevel' in level) ? 'new' : level.id;
		$('.levels-edit-tabs a.nav-link').each(function() {
			var href = $(this).data('href');
			var id = href.replace('#', '');
			$('div[data-id="' + id + '"]').attr('id', id + '-' + xid);
			$(this).attr('href', href + '-' + xid);
		});

		$('a[href="' + window.parent.location.hash + '"].nav-link').each(function() {
			$(this).tab('show');
		});

		$('[name=custom_reg_form]').change();
		$('.expire_settings :input').change();
		$('#levels_addremove_from select').change();
		$('#levels_actions select').change();

		$('#levels-create .-is-saving').removeClass('is-saving');
		if(save_only !== true) {
			$('#levels-create').show();
		}
		$('.levels-richtext').addClass('richtext').removeClass('levels-richtext');

		this.show_level_actions(level.id);

		$('body').trigger('wishlistmember_edit_level', level );

		this.loading_form = false;
		wlm.is_saving( 'body', false );
	},
	_close_and_list: function(e) {
		e.preventDefault();
		window.parent.location.hash = '#';
	},
	_save_and_continue: function(e, reload, msg) {
		try {
			e.preventDefault();
		} catch (e) {}
		e.data.that._save(e, false, reload, msg);
	},
	_save: function(e, close, reload, msg) {
		if(!this.loading_form) {
			$(e.currentTarget).closest('.form-group').addClass('-is-saving');
		}
		$('.modal-loader-overlay-holder').removeClass('d-none');

		var caller = arguments.callee.caller.name;
		if(this.is_saving === true && caller != 'on_done') {
			this.save_queue.push(arguments);
			return;
		}
		this.is_saving = true;

		var st;
		var $data = {};
		var triggeredBy = $(e.currentTarget);

		var first_save = false;
		if($('#first-save').val() == 'yes'){
			st = $('form#level-form');
			first_save = true;
		} else {
			if($(e.target).is(':input') && !$(e.target).hasClass('btn')) {
				var myparent = $(e.target).closest('.form-group');
				if(myparent.length) {
					myparent.addClass( 'has-success' );
					window.setTimeout( function( container ) {
						container.removeClass( 'has-success' );
					}, 2000, myparent );
				}

				st = $(e.target).closest('div');
			} else {
				st = $(e.target).closest('.save-section');
				if(!st.length) {
					st = $(e.target).closest('.modal')
				}
				if(!st.length) {
					st = $('form#level-form');
				}

				if(st.data('save-section')) {
					st = $(st.data('save-section'));
				}
			}
			if(st.not('#level-form')) {
				$data = $('#save-action-fields').get_form_data();
			}
		}

		st.find(':checkbox').not('[uncheck_value]').attr('uncheck_value', '');
		st.find('select').each(function() {
			if(!this.value && this.name) {
				$data[this.name] = '';
			}
		});

		// do not save .extra-tabs - these are tabs added by something external (i.e. another plugin)
		// it is upto the external plugin to do their own saving
		if(true || st.find('.extra-tabs').length) {
			// save select fields first as .clone won't save their values properly
			var sels = st.find('select');
			
			// remopve .extra-tabs
			st = st.clone();
			st.find('.extra-tabs').remove();
			
			// restore value of select fields
			sels.each((i,sel) => {
				sel.name && st.find('select[name="' + sel.name + '"]').val($(sel).val());
			})
		}

		var f = st.save_settings({
			data: $data,
			lobj: this,
			on_init: function() {
				if(triggeredBy.hasClass('btn')) {
					triggeredBy.disable_button({disable:true, icon:'update', class:''});
				}
			},
			on_done: function() {
				$('#first-save').val('');

				$('.modal-loader-overlay-holder').addClass('d-none');
				$('.-is-saving').removeClass('-is-saving');

				this.lobj.is_saving = false;
				if(this.lobj.save_queue.length) {
					this.lobj._save.apply(this.lobj, this.lobj.save_queue.shift());
				} else {
					this.lobj.is_saving = false;
					if(triggeredBy.hasClass('btn')) {
						triggeredBy.disable_button({disable:false, icon:'save', class:''});
					}
				}
			},
			on_error: function($me, $error_fields) {
				var msgs = [];
				$.each($error_fields, function(key, obj) {
					if ( typeof obj == 'object' ) {
						obj.parent().addClass('has-error');
						if(obj.attr('name')=='name') {
							msgs.push(wlm.translate( 'Level name required' ));
						}
					}
				});
				if(msgs.length) {
					$('.wlm-message-holder').show_message({type: 'error', message: msgs.join(', ')});
				} else {
					$('.wlm-message-holder').show_message({type: 'error', message: WLM3VARS.request_error });
				}
			},
			on_success: function($me, $result) {
				$('#all-level-data').removeClass('d-none');
				wpm_levels = $result.wpm_levels;
				$.each(wpm_levels, function(lid, level){
					level.id = lid;
				});
				$( 'form#level-form :input[data-initial]' ).each( function () {
					$( this ).data( 'initial', $( this ).val() );
				} );

				$('.expire_settings').data('initial', $('.expire_settings :input').serialize());

				$('.wlm-message-holder').show_message({message: (msg ? msg : $result.msg)});
				if (close) {
					if(close === true) {
						e.data.that._close_and_list(e);
					} else {
						$(close).modal('hide');
					}
				}
				lid = $me.find(':input[name=id]:first').val();
				if (reload || first_save) {
					var url = 'admin.php?page=WishListMember&wl=setup/levels&level_id=' + lid + '#levels_access-' + lid;
					$('#the-content').load_screen(url, document.title, true);
					return;
				}

				if(!(lid in level_stats.active)) {
					level_stats.active[lid] = [0];
					level_stats.cancelled[lid] = [0];
					level_stats.forapproval[lid] = [0];
					level_stats.unconfirmed[lid] = [0];
					level_stats.expired[lid] = [0];
				}
			}
		});
	},
	_save_changes: function(e) {
		if($(e.target).closest('.extra-tabs').length) {
			// skip extra tabs added by wishlistmember_level_edit_tabs action
			return;
		}
		if(e.target.name == "sched_toggle") return null;
		if(e.target.name == "inheritparent") return null;
		if(e.data.that.loading_form) return null;
		e.data.that._save_and_continue(e);
		return;
	},
	_edit: function(levelid) {
		var level = $.extend({}, WLM3VARS.level_defaults);

		if(typeof levelid == 'object') {
			$.extend(level, levelid);
			levelid = level.id;
		} else {
			$.extend(level, wpm_levels[levelid]);
			level.id = levelid;
		}
		
		level.require_email_confirmation_start=Math.abs(level.require_email_confirmation_start)||WLM3VARS.level_defaults.require_email_confirmation_start;
		level.require_email_confirmation_send_every=Math.abs(level.require_email_confirmation_send_every)||WLM3VARS.level_defaults.require_email_confirmation_send_every;
		level.require_email_confirmation_howmany=Math.abs(level.require_email_confirmation_howmany)||WLM3VARS.level_defaults.require_email_confirmation_howmany;

		if (parseInt('0' + level.noexpire) > 0) {
			level.expire_option = 0;
		} else {
			if (parseInt('0' + level.expire_option) < 1) {
				level.expire_option = 1;
			}
		}

		var levels_addremove = $.extend({}, js_levels);

		var levels_addremove = $.map(js_levels, function(value, index) {
			if(value.value != levelid) {
				return [value];
			}
		});

		$('#levels_addremove_from select').empty().select2({'data' : levels_addremove}, true);
		$('#levels_actions select').empty().select2({'data' : levels_addremove}, true);

		$("[name='action_levels']").empty().select2({'data' : levels_addremove}, true);

		if(!level.name) {
			$('#all-level-data').addClass('d-none');
		}

		this.show_form(level);
	},
	_clone: function(e) {
		// clone level info
		var level = $.extend({}, WLM3VARS.level_defaults);
		var level_id = $(e.target).closest('tr').data('id');
		$.extend(level, wpm_levels[level_id]);
		level.id = parseInt(new Date().getTime() / 1000);
		level.levelOrder = level.id;
		level.url = wlm.random_string();
		var lname = level.name;
		level.name = wlm.translate( 'Copy of ' ) + level.name;
		level.newlevel = true;
		level.clone = level_id;
		level.count = 0;

		level.action = 'admin_actions';
		level.WishListMemberAction = 'save_membership_level';

		$('<div/>').save_settings({
			data : level,
			on_done : function() {
				var url = 'admin.php?page=WishListMember&wl=setup/levels&level_id=' + level.id + '#levels_access-' + level.id;
				$('#the-content').load_screen(url, document.title, true);
			}
		});
	},
	_new: function() {
		// initial level
		var level = $.extend({}, WLM3VARS.level_defaults);
		level.id = parseInt(new Date().getTime() / 1000);
		level.levelOrder = level.id;
		level.url = wlm.random_string();
		level.newlevel = true;
		level.noexpire = 1;
		level.expire_option = 0;
		// call edit
		this._edit(level);
		$('#first-save').val('yes');
		$('#levels-create :input[name=name]').focus();
	},
	_delete: function(e) {
		e.preventDefault();
		tr = $(this).closest('tr');
		var levelid = $(this).closest('tr').attr('data-id');
		var f = $('<form><input type=hidden name=action value=admin_actions><input type=hidden name=WishListMemberAction value=delete_membership_level><input type=hidden name=id value=' + levelid + '></form>');
		f.save_settings({
			on_success: function($me, $result) {
				wpm_levels = $result.wpm_levels;
				$('.wlm-message-holder').show_message({
					message: $result.msg,
					type: $result.msg_type,
					icon: $result.msg_type
				});
				if($result.success) {
					tr.remove();
					if(!$('#levels-list-table tbody tr').length) {
						$('#levels-list-table tbody').replaceWith('<tbody/>');
					}
				}
			}
		});

		return false;
	},
	modals: function() {
		var wlm3sl = this;
		// header footer modal
		new wlm3_modal(
			'#header-footer-modal', {
				save_handler: function(event) {
					event.data.modal.close();
					return false;
				},
				after_open: function() {
					$('#header-footer .nav-tabs a.nav-link').first().tab('show');
				},
				before_close: this.modal_close,
			}
		);

		// auto-create-accounts-for-integrations modal
		new wlm3_modal(
			'#auto-create-accounts-for-integrations-modal', {
				save_handler: function(event) {
					event.data.modal.close();
					return false;
				},
				after_open: function() {
				},
				before_close: this.modal_close,
			}
		);

		// email notifications modal
		new wlm3_modal(
			'#email-notification-settings-modal', {
				save_handler: function(event) {
					event.data.modal.close();
					return false;
				},
				before_open: function(event) {
					var btn = $(event.relatedTarget);

					var modal = $('#email-notification-settings');

					modal.data('save-section', '.-holder.' + btn.data('notif-setting'));

					var modalbody = modal.find('.modal-body').first();

					modalbody[0].className = 'modal-body';
					modalbody.addClass(btn.data('notif-setting'));

					var modaltitle = modal.find('.modal-title').first();
					modaltitle.find('span').text(btn.data('notif-title'));

					$('#email-notification-settings .-holder.' + btn.data('notif-setting') + ' .nav-tabs a:first').click();
				},
				before_close: this.modal_close,
			}
		);

		// terms and conditions modal
		new wlm3_modal(
			'#terms-and-conditions-modal', {
				save_handler: function(event) {
					event.data.modal.close();
					return false;
				},
				before_open: function(event) {
					return true;
				},
				before_close: this.modal_close,
			}
		);

		// custom redirects modal
		new wlm3_modal(
			'#custom-redirects-modal', {
				save_handler: function(event) {
					event.data.modal.close();
					return false;
				},
				before_open: function(event) {
					var btn = $(event.relatedTarget);

					var modal = $('#custom-redirects');
					var modalbody = modal.find('.modal-body').first();

					modalbody[0].className = 'modal-body';
					modalbody.addClass(btn.data('notif-setting'));

					var modaltitle = modal.find('.modal-title').first();
					modaltitle.find('span').text(btn.data('notif-title'));

					modal.find('.redirect-type-toggle').each(function(index, obj) {
						obj = $(obj);
						var type = obj.find('input.-redirect-type:checked').val();
						var parent = obj.closest('.-holder');
						parent.find('.redirect-type').hide();
						parent.find('.redirect-type.type-' + type).show();
					});

					$('#custom-redirects .-holder.' + btn.data('notif-setting') + ' .nav-tabs a:first').click();
				},
				before_close: this.modal_close,
			}
		);

		// recaptcha modal
		new wlm3_modal(
			'#recaptcha-settings-modal', {
				save_handler: function(event) {
					var close = false;
					switch($(event.currentTarget).data('btype')) {
						case 'save-close':
							close = true;
						case 'save':
							$('form#recaptcha-settings-form').save_settings();
							var x = $('form#recaptcha-settings-form').get_form_data();
							_.assign(recaptcha_settings, _.pick(x, _.keys(recaptcha_settings)));
						break;
						case 'cancel':
							close = true;
					}
					if(close) {
						event.data.modal.close();
					}
					return false;
				},
				before_open: function(event) {
					$('form#recaptcha-settings-form').set_form_data(recaptcha_settings);
					return true;
				}
			}
		);

		// level-actions modal
		new wlm3_modal(
			'#level-actions-modal', {
				save_handler: function(event) {
					var settings_data = {
						action : "admin_actions",
						WishListMemberAction : "save_level_actions",
						levelid : $('input[name=id]').val(),
					};
					var x = $('#level-action-data').save_settings({
						data: settings_data,
					    on_success: function( $me, $result) {
					    	wlm3sl.show_level_actions($('input[name=id]').val());
					    	$(".wlm-message-holder").show_message({message:$result.msg, type:$result.msg_type, icon:$result.msg_type});
					    },
					    on_fail: function( $me, $data) {
					    	alert(WLM3VARS.request_failed);
					    },
					    on_error: function( $me, $error_fields) {
					    	$.each( $error_fields, function( key, obj ) {
								if ( typeof obj == 'object' ) {
									obj.parent().addClass('has-error');
								}
							});
					    },
					    on_done: function( $me, $data) {
					    	event.data.modal.close();
					    }
					});
					return false;
				},
				before_open: function(event) {
					$(".schedule-ondate-holder").hide();
					$(".schedule-after-holder").show();
					$(".inheritparent-holder").hide();

					//clear error fields
					$("#level-action-data :input").each(function(){
					 	$(this).parent().removeClass("has-error");
					});

					$("[name='level_action_event']").prop("required", true);
					$("[name='level_action_method']").prop("required", true);

					$("[name='action_levels']").prop("required", true);
					$("[name='ppp_content']").prop("required", true);
					$("[name='ppp_title']").prop("required", true);


					$("[name='level_action_id']").val("");
					$("#level-actions").find(".modal-title").html(wlm.translate( 'Add Level Actions' ));
					$("#level-actions").find(".save-button span").html(wlm.translate( 'Add Action' ));
					$("#level-actions").find(".save-button .wlm-icons").html("add");

					$("[name='level_action_method']").val("").trigger('change.select2');
					$("[name='level_action_event']").val("").trigger('change.select2');
					$("[name='level_action_method']").prop("disabled", true);

					$("[name='action_levels']").val("").trigger('change.select2');
					$("[name='level_email']").val("sendlevel").trigger('change.select2');
					$("[name='sched_ondate']").val("");
					$("[name='sched_after_term']").val("");

					$(".sched-options-holder").hide();
					$(".wlm-levels-holder").hide();
					$(".inherit-levels-holder").hide();

					$(".ppp-options-holder").hide();
					$("[name='ppp_title']").val("Private-{username}-{timestamp}");
					$("[name='ppp_type']").val("post").trigger('change.select2');
					$("[name='ppp_content']").val("").trigger('change.select2');
					return true;
				},
				before_close: function(event) {
					$("[name='level_action_event']").prop("required", false);
					$("[name='level_action_method']").prop("required", false);

					$("[name='action_levels']").prop("required", false);
					$("[name='ppp_content']").prop("required", false);
					$("[name='ppp_title']").prop("required", false);
					return true;
				},
			}
		);
	},
	init: function() {
		this.list_levels();
		this.modals();
		var wlm3sl = this;
		$('#levels-list-table tbody').sortable({
			items: '> tr',
			handle: '.handle',
			axis: 'y',
			stop: function(event, ui) {
				var data = {
					action : 'wlm_reorder_membership_levels',
					reorder : {}
				}
				var order = 1;
				var lid = 0;
				$('#levels-list-table tbody > tr').each(function() {
					lid = $(this).data('id');
					wpm_levels[lid].levelOrder = order;
					data.reorder[lid] = order;
					order++;
				});

				$.post(WLM3VARS.ajaxurl, data, function(result) {
					$.each(data.reorder, function(i, o) {
						$('#levels-list-table tbody > tr[data-id='+i+']').attr('data-sort', o);
					});
					js_levels = [];
					$.each(result, function(i, o) {
						js_levels[o.levelOrder] = {
							'value' : i,
							'text' : o.name,
							'id' : i,
							'name' : o.name,
						};
					});
					js_levels = Object.values(js_levels);
				});
			},
			start: function(event, ui) {
				$('#levels-list-table thead th').each(function(index, el) {
					var el = $(el);
					if(el.width()) {
						$(ui.item).find('td:eq(' + index + ')').width(el.width());
					}
				});
			}
		});

		$('#levels-create, #levels-list').undelegate('.wlm3levels');

		$('#levels-create').on( 'click.wlm3levels', 'button', function( e ) { e.preventDefault() } );

		$('#levels-create')
			.on('keyup.wlm3levels', '[name=name]', function() {
				$(this).attr('placeholder', wlm.translate( 'Level Name Required' ) );
			})
			// set expire_option based on noexpire
			.on('change.wlm3levels', '[name=expire_option]', function() {
				var initial = $('.expire_settings').data('initial');
				var newval = $('.expire_settings :input').serialize();

				var v = $(this).val();
				$('#levels-create [name=noexpire]').val(v == '0' ? 1 : 0);
				switch(v) {
					case '2': // specific date
						// set date to current
						if(initial && initial != newval) {
							var xd = moment();
							$('[name="expire_date"]').val(xd.format(WLM3VARS.js_date_format));
							var picker = $('[name="expire_date"]').data('daterangepicker');
							picker.setStartDate(xd);
							picker.setEndDate(xd);
						}

						$('.expire_specific_date').show();
						$('.expire_fixed_term').hide();
					break;
					case '1': // fixed term
						// reset values to default
						if(initial && initial != newval) {
							$('[name="expire"]').val(WLM3VARS.level_defaults.expire);
							$('[name="calendar"]').val(WLM3VARS.level_defaults.calendar).change();
						}

						$('.expire_fixed_term').show();
						$('.expire_specific_date').hide();
					break;
					default:
						$('.expire_fixed_term, .expire_specific_date').hide();
				}
				wlm3sl.expire_hide_show_notif_button();
			})
			.on('change.wlm3levels', '.expire_settings :input', function() {
				var initial = $('.expire_settings').data('initial');
				var newval = $('.expire_settings :input').serialize();

				if(initial == newval) {
					wlm3sl.expire_hide_show_notif_button();
					$('.expire_apply').hide();
				}else{
					$('.expire_notification').hide();
					$('.expire_apply').show();
				}
			})
			.on('click.wlm3levels', '.expire_settings .expire_apply .btn.-success', { that: this }, function(e) {
				e.data.that._save_and_continue(e);
				$('.expire_settings').addClass('has-success');
				window.setTimeout( function() {
					$('.expire_settings').removeClass('has-success');
				}, 2000 );

				wlm3sl.expire_hide_show_notif_button();
				$('.expire_apply').hide();
			})
			.on('click.wlm3levels', '.expire_settings .expire_apply .btn.-bare', { that: this }, function(e) {
				var initial;
				initial = wlm.parse_string($('.expire_settings').data('initial'));
				$('.expire_settings').set_form_data(initial);
				$('[name=expire_option]').change();

				wlm3sl.expire_hide_show_notif_button();
				$('.expire_apply').hide();
			})
			// modal save button handler
			.on('click.wlm3levels', 'button.modal-save-and-continue', {
				that: this
			}, this._save_and_continue)
			// modal save and close button handler
			.on('click.wlm3levels', 'button.modal-save-and-close', {
				that: this
			}, function(e) {
				e.preventDefault();
				e.data.that._save(e, $(this).closest('.modal'));
			})
			// modal cancel button handler
			.on('click.wlm3levels', 'button.modal-cancel', {
				that: this
			}, function(e) {
				e.preventDefault();
				$(this).closest('.modal').modal('hide');
			})
			// done button handler
			.on('click.wlm3levels', 'button.done', {
				that: this
			}, this._close_and_list)
			// redirect type toggle
			.on('click.wlm3levels', '.redirect-type-toggle input[type=radio]', function() {
				var type = $(this).val();
				var parent = $(this).closest('.-holder');
				parent.find('.redirect-type').hide();
				parent.find('.redirect-type.type-'+type).show();
				return true;
			})
			.on('click.wlm3levels', '.create-page-btn', function( e ) {
				e.preventDefault();
				var btn = $(this);
				var input = btn.closest('.row').find('input.create-page');
				input.closest('.form-group').removeClass('has-error');
				var title = input.val().trim();

				if(!title) {
					input.closest('.form-group').addClass('has-error');
				} else {
					var default_message = $(this).closest('.-holder').find('textarea.form-control').attr('name');
					var data = {
						action : 'admin_actions',
						WishListMemberAction : 'create_system_page',
						page_title : title,
						page_content : WLM3VARS.level_defaults[default_message]
					}
					$('<div/>').save_settings({
						data: data,
						on_init: function($me, $data) {
							btn.disable_button({disable:true});
						},
						on_success: function($me, $data) {
							$('.wlm-message-holder').show_message({
								message: $data.msg,
								type: $data.msg_type
							});
							var select = btn.closest('.redirect-type').find('.system-page');
							select.prepend($('<option/>', {value : $data.post_id, text : $data.post_title })).val($data.post_id).trigger('change.select2');
							input.val('');
						},
						on_done: function($me, $data) {
							btn.disable_button({disable:false});
							btn.closest('.collapse').collapse('hide');
						}
					});
				}
			})
			.on('change.wlm3levels', '#levels_addremove_from select', function() {
				$('#levels_addremove_from select option').removeProp('disabled');
				$('#levels_addremove_from select option').prop('disabled', false);
				$('#levels_addremove_from select').each(function() {
					$.each($(this).val(), function(i, v) {
						$('#levels_addremove_from select option[value='+v+']').not(':selected').prop('disabled', true);
					});
				})
				$('#levels_addremove_from select').select2();
			})
			.on('change.wlm3levels', '#levels_actions select', function() {
				var action_holder = $(this).closest(".tab-pane");
				action_holder.find('select option').removeProp('disabled');
				action_holder.find('select option').prop('disabled', false);
				action_holder.find('select').each(function() {
					$.each($(this).val(), function(i, v) {
						action_holder.find('select option[value='+v+']').not(':selected').prop('disabled', true);
					});
				})
				action_holder.find('select').select2();
			})
			.on('change.wlm3levels', ':checkbox[name]:not(.modal-input), :checked[name]:not(.modal-input), input[name][type=text]:not(.modal-input, [name=name], [name=url], [name=expire], [name=expire_date]), [name=removeFromLevel], [name=addToLevel], [name=cancelFromLevel], [name=cancel_removeFromLevel], [name=cancel_addToLevel], [name=cancel_cancelFromLevel], [name=remove_removeFromLevel], [name=remove_addToLevel], [name=remove_cancelFromLevel], [name=disableexistinglink], [name=custom_reg_form], [name=role]', {
				that: this
			}, this._save_changes)
			.on('click.wlm3levels', '.create-form-button', {that : this}, function(e) {
				e.preventDefault();
				var parent = $('#new-reg-form-name').parent();
				if(!$('#new-reg-form-name').val().trim()) {
					parent.addClass('has-error');
					return false;
				}
				var data = {
					form_name: $('#new-reg-form-name').val(),
					rfdata: wpm_regform_defaults,
					form_fields: 'firstname,lastname',
					form_id: 'CUSTOMREGFORM-' + new Date().getTime(),
					action: 'admin_actions',
					WishListMemberAction: 'save_custom_registration_form'
				}
				parent.removeClass('has-error');
				$('<div/>').save_settings({data : data, on_done : function(me, data, jqXHR, textStatus) {
					if(wlm.json_parse(jqXHR).success) {
						$('[name=custom_reg_form]').append( new Option( data.form_name, data.form_id, true, true) ).change();
						$('#create_reg_form').collapse('hide');
						$('#new-reg-form-name').val('');
					} else {
						$('.wlm-message-holder').show_message({type:'danger',message:wlm.translate( 'Cannot Create Custom Registration Form' )})
						parent.addClass('has-error');
					}
				}});

				return false;
			})
			.on('change.wlm3levels', '[name=custom_reg_form]', function(e) {
				var target = $(this).closest('.row').find('.edit-custom-regform');
				if(this.selectedIndex) {
					target.removeClass('d-none');
				} else {
					target.addClass('d-none');
				}
			})
			.on('click.wlm3levels', '.edit-custom-regform-btn', function(e) {
				var link = $(this).data('link') + $('[name=custom_reg_form]').val();
				window.open(link, '_blank');
			})
			.on('click.wlm3levels, change.wlm3levels', ':input.-sender-default-toggle', function(e) {
				if(this.checked) {
					$('#' + this.name + ' .form-control').addClass('d-none');
					$('#' + this.name + ' .form-control.-sender-default').removeClass('d-none');
				} else {
					$('#' + this.name + ' .form-control').addClass('d-none');
					$('#' + this.name + ' .form-control:not(.-sender-default)').removeClass('d-none');
				}
			});

		$('#levels-list')
			// clone button handler
			.on('click.wlm3levels', '.-edit-btn, .-new-btn, .-clone-btn', {that:this}, function (e) {
				if(e.shiftKey || e.ctrlKey || e.altKey || e.metaKey) {
					return true;
				}

				e.preventDefault();

				var level = '';
				if($(this).hasClass('-clone-btn')) {
					level = 'clone';
					e.data.that._clone(e);
					return false;
				} else {
					$('#the-content').load_screen($(this), document.title);
					return false;
				}
			});

		if(window.parent.location.hash) {
			var hash = window.parent.location.hash.split('-');
			var action = hash.shift();
			wlm_level_edit_tabs.push('access','registrations','requirements','additional_settings','notifications','actions');
			var regex = '^(#levels_(' + wlm_level_edit_tabs.join('|') + '))$';
			if(action.match(new RegExp(regex))) {
				var level = hash.join('-');
				if(level == 'new') {
					this._new();
				} else {
					this._edit(level);
				}
			}
			wlm.pushState('hash-only', '', window.parent.location.hash, true);
		}

		$('#table-level-actions').on('click.wlm3levels', '.edit-action-btn', function(e) {
			var actionid = $(this).attr("actionid");
			var settings_data = {
				action : "admin_actions",
				WishListMemberAction : "get_level_action_details",
				actionid : actionid,
			};
			var x = $('#table-level-actions').save_settings({
				data: settings_data,
			    on_init: function( $me, $data) {
			    	$('#level-actions').modal('toggle');
			    	$("#level-actions").find(".modal-title").html(wlm.translate( 'Loading action details...' ));
			    },
			    on_success: function( $me, $result) {
			    	if ( $result.success ) {
			    		var action = $result.action;
						$("#level-actions").find(".modal-title").html(wlm.translate( 'Update Level Actions' ));
						$("#level-actions").find(".save-button span").html(wlm.translate( 'Update Action' ));
						$("#level-actions").find(".save-button .wlm-icons").html("update");

			    		$("[name='level_action_id']").val(action.ID);
			    		$("#level-actions [name='level_action_event']").val(action.option_value.level_action_event).trigger('change.select2');
			    		$("#level-actions [name='level_action_method']").val(action.option_value.level_action_method).trigger('change.select2');
			    		$("#level-actions [name='action_levels']").val(action.option_value.action_levels).trigger('change.select2');
			    		$("#level-actions [name='level_email']").val(action.option_value.level_email).trigger('change.select2');

			    		$("#level-actions [name='level_action_method']").prop("disabled", false);
			    		$("#level-actions [name='level_action_method']").trigger("change");

						if ( action.option_value.level_action_method == "add-ppp" || action.option_value.level_action_method == "create-ppp" || action.option_value.level_action_method == "remove-ppp"  ) {
							$("#level-actions [name='ppp_type']").val(action.option_value.ppp_type).trigger('change.select2');
							$("#level-actions [name='ppp_type']").trigger("change");

						    var option = new Option( action.option_value.ppp_post_title, action.option_value.ppp_content, true, true);
						    $("#level-actions [name='ppp_content']").append(option).trigger('change.select2');
							$("#level-actions [name='ppp_content']").val(action.option_value.ppp_content).trigger('change.select2');

							$("#level-actions [name='ppp_title']").val(action.option_value.ppp_title);
						}

						if ( action.option_value.level_action_method == "add" ) {
							$(".inheritparent-holder").show();
							if ( action.option_value.inheritparent == "1" ) {
								$("#level-actions [name='inheritparent']").prop( "checked", true );
							} else {
								$("#level-actions [name='inheritparent']").prop( "checked", false );
							}
						} else {
							$(".inheritparent-holder").hide();
						}


			   			$("#level-actions .schedule-ondate-holder").hide();
						$("#level-actions .schedule-after-holder").hide();
						if ( action.option_value.sched_toggle == "ondate" ) {
							$("#level-actions .sched-ondate").prop("checked", true);
							$("#level-actions .schedule-ondate-holder").show();
							$("#level-actions [name='sched_ondate']").val(action.option_value.sched_ondate);
						} else {
							$("#level-actions .sched-after").prop("checked", true);
							$("#level-actions .schedule-after-holder").show();
							$("#level-actions [name='sched_after_term']").val(action.option_value.sched_after_term);
							$("#level-actions [name='sched_after_period']").val(action.option_value.sched_after_period).trigger('change.select2');
						}

			    	} else {
			    		$(".wlm-message-holder").show_message({message:$result.msg, type:$result.msg_type, icon:$result.msg_type});
			    		$('#level-actions').modal('toggle');
			    	}
			    },
			    on_fail: function( $me, $data) {
			    	$(".wlm-message-holder").show_message({message:WLM3VARS.request_failed, type:'danger', icon:'danger'});
			    	$('#level-actions').modal('toggle');
			    },
			    on_error: function( $me, $error_fields) {
			    	$(".wlm-message-holder").show_message({message:WLM3VARS.request_error, type:'danger', icon:'danger'});
			    	$('#level-actions').modal('toggle');
			    },
			});
		})
	},
	show_level_actions: function(levelid) {
		var settings_data = {
			action : "admin_actions",
			WishListMemberAction : "get_level_actions",
			levelid : levelid,
		};
		var default_html = "<tr><td colspan='3'>No action is set</td></tr>";
		$('#table-level-actions .action-table-title').html( $("input[name='name']").val() +" " +wlm.translate( 'Level Actions' ) );
		var x = $('#table-level-actions').save_settings({
			data: settings_data,
		    on_init: function( $me, $data) {
		    	if ( $me.find("tbody tr").length <= 0 ) {
		    		$me.find("tbody").html("<tr><td colspan='3'>Loading actions, please wait...</td></tr>");
		    	}
		    },
		    on_success: function( $me, $result) {
		    	var html = "";
		    	$.each( $result.actions, function (index, value) {
		            html += "<tr class='button-hover level-action-tr-" +value.ID +"'>";
		            html += "<td>" +value.option_value.action_text +"</td>";
		            html += "<td>" +value.option_value.schedule +"</td>";
		            html += "<td class='text-right'><div class='btn-group-action'>";
		            	html += "<a href='#' title='Edit Action' actionid='" +value.ID +"' class='edit-action-btn'><span class='wlm-icons md-24 -icon-only'>edit</span></a>&nbsp;&nbsp;";
		            	html += "<a href='#' title='Delete Action' actionid='" +value.ID +"' class='delete-action-btn'><span class='wlm-icons md-24 -icon-only'>delete</span></a>";
		            html += "</div></td>";
		            html += "</tr>";
		        })
		        if ( !html ) html = default_html;
		    	$me.find("tbody").html(html);

				$me.find('.delete-action-btn').do_confirm({placement:'right',yes_classes:'-success', confirm_message : wlm.translate( 'Are you sure you want to delete this action?' )}).on('yes.do_confirm', function() {
					var actionid = $(this).attr("actionid");
					var parent = $(this).closest("tr");
					var settings_data = {
						action : "admin_actions",
						WishListMemberAction : "delete_level_action",
						actionid : actionid,
					};
					var x = $('#table-level-actions').save_settings({
						data: settings_data,
					    on_success: function( $me, $result) {
					    	if ( $result.success ) {
					    		parent.fadeOut(500, function(){
					    			$(this).remove();
							    	if ( $me.find("tbody tr").length <= 0 ) {
							    		$me.find("tbody").html(default_html);
							    	}
					    		});
					    		$(".wlm-message-holder").show_message({message:$result.msg, type:$result.msg_type, icon:$result.msg_type});
					    	} else {
					    		$(".wlm-message-holder").show_message({message:$result.msg, type:$result.msg_type, icon:$result.msg_type});
					    	}
					    },
					});
				});
		    },
		});
	}
};
$(function() {
	wlm3sl = new wlm3_screen_levels();
	$('#header-footer .nav-tabs a.nav-link').on('shown.bs.tab', function() {
		var textarea = $($(this).data('target') + ' textarea').first();
		if(textarea.hasClass('codemirrored')) return;
		textarea.addClass('codemirrored');
		var mode = {
			lineNumbers: true,
			mode: "text/html"
		};
		wp.CodeMirror.fromTextArea(textarea[0], mode ).on('change', function(cm, obj) {
			$(cm.getTextArea()).val(cm.getValue());
		});
	});
	if(!window.parent.location.hash) {
		$('#levels-list').show();
	}

	$('.wlm-datetimepicker').daterangepicker({
		opens: 'center',
		singleDatePicker: true,
		timePicker: true,
		timePickerIncrement: 15,
		showCustomRangeLabel: false,
		startDate: moment(),
		minDate: moment(),
		buttonClasses: "btn -default",
		applyClass: "-condensed -success",
		cancelClass: "-condensed -link",
		autoUpdateInput: false,
		locale: {
			format: "MM/DD/YYYY hh:mm a"
		}
	});
	$('.wlm-datetimepicker').on('apply.daterangepicker', function(ev, picker) {
		$(this).val(picker.startDate.format("MM/DD/YYYY hh:mm a"));
	});

	$('.toggle-radio-sched').click(function() {
		var holder = $(this).closest('.row');
		var value = $(this).val();
		$(".schedule-holder").hide();
		$(".schedule-" +value +"-holder").show();
	});

	$("[name='level_action_method'],[name='level_action_event']").on("change", function (e) {

		if ( $("[name='level_action_event']").val() == ""  ) {
			$("[name='level_action_method']").prop("disabled", true);
		}  else {
			$("[name='level_action_method']").prop("disabled", false);
		}

		if ( $("[name='level_action_method']").val() == "add" ) {
			$(".inheritparent-holder").show();
		} else {
			$(".inheritparent-holder").hide();
		}

		if ( $("[name='level_action_method']").val() == "remove" ) {
			$(".wlm-levels-notification").parent().hide();
		} else {
			$(".wlm-levels-notification").parent().show();
		}

		$(".ppp-options-holder").hide();
		$(".sched-options-holder").hide();
		$(".wlm-levels-holder").hide();
		$(".inherit-levels-holder").hide();
		if ( $("[name='level_action_method']").val() != "" ) {
			if ( $("[name='level_action_method']").val() == "add-ppp" ||  $("[name='level_action_method']").val() == "create-ppp" ||  $("[name='level_action_method']").val() == "remove-ppp" ) {
				$(".ppp-options-holder").show();
				$("[name='ppp_type']").trigger("change");
				if ( $("[name='level_action_method']").val() == "add-ppp" || $("[name='level_action_method']").val() == "remove-ppp" ) {
					$(".ppp-options-title-holder").hide();
					$("[name='ppp_content']").parent().find("label").html("Select Content");
					$("[name='ppp_title']").prop("required", false);
				} else {
					$(".ppp-options-title-holder").show();
					$("[name='ppp_content']").parent().find("label").html("Select Content to Copy");
					$("[name='ppp_title']").prop("required", true);
				}
				$("[name='action_levels']").prop("required", false);
				$("[name='ppp_content']").prop("required", true);
			} else {
				$(".sched-options-holder").show();
				$(".wlm-levels-holder").show();
				$(".inherit-levels-holder").show();
				$("[name='action_levels']").prop("required", true);
				$("[name='ppp_content']").prop("required", false);
				$("[name='ppp_title']").prop("required", false);
			}
		}
	});

	$("[name='ppp_type']").on("change", function (e) {
		update_ppp_list();
	});

	$('#show-actions-tab').click(function() {
		$('.nav-tabs a[data-href="#levels_actions"]').tab('show');
	});

	$('.dismiss-addto-message').click(function() {
		var parent = $(this).closest(".col-md-7");
		$(this).remove();
		parent.addClass("text-muted");
		var settings_data = {
			action : "admin_actions",
			WishListMemberAction : "save",
			addto_feature_moved : 0,
		};
		var x = $(this).save_settings({
			data: settings_data,
		    on_success: function( $me, $result) {
		    	if ( $result.success ) {
		    		parent.fadeOut(500, function(){
		    			$(this).remove();
		    		});
		    	} else {
		    		parent.removeClass("text-muted");
		    	}
		    },
		});
	});
});

var update_ppp_list = function() {
	var post_type = $("[name='ppp_type']").val();
	var post_title = $("[name='ppp_type']").find("option:selected").text();

	var plchldr = wlm.translate( 'Search for Pay Per Posts (Posts Only)' );
	plchldr = post_title != 'Posts' ? wlm.translate( 'Search for Pay Per Post $$1 ($$2 Only)', [post_title,post_title] ) : plchldr;

	select = $("[name='ppp_content']");
	if ( select.data('select2') ) select.select2('destroy');
	//only display the post type selected
	select.select2({
		ajax: {
		    url: WLM3VARS.ajaxurl,
		    dataType: 'json', delay: 500, type: 'POST',
		    data: function (params) {
		      return {
		        search:  params.term || "", page: params.page || 0, page_limit: 16,
				action: 'admin_actions', WishListMemberAction : 'payperpost_search',
				ptype: post_type
		      };
		    },
		    processResults: function (data) {
		        var arr = []
		        $.each( data.posts, function (index, value) {
		            arr.push({ id: value.ID, text: value.post_title })
		        })
				var more = ( data.page * data.page_limit ) < data.total;
				return {results: arr, pagination: {more: more}};
			},cache: true
		},
		minimumInputLength: 1,
		placeholder: plchldr,theme:"bootstrap",
		language: {
	        noResults: function(){
	           if (  post_title != 'Posts' ) return wlm.translate( 'No Pay Per Post $$1 found', [post_title.slice(0, -1)] );
	           else return wlm.translate( 'No Pay Per Post found' );
	        }
		},
	});
	select.val("").trigger('change.select2');
}
