jQuery(function($){
    $('.skip-license').click(function() {
        $('input[name="license"]').val('****************');
        $('.save-btn[data-screen="license"]').click();
    });
    $('.next-btn').click( process_next );
    $('.save-btn').click( process_wizard );
    $(".isexit").do_confirm({confirm_message : wlm.translate( 'Unsaved data will be lost. Exit Wizard?' ), placement: 'right'}).on("yes.do_confirm", process_wizard );

    window.onpopstate = function(event) {
        if ( window.location.hash ) {
            var step = window.location.hash.substring(1);
            console.log(step);
            if ( step == "congrats" ) {
                $('#the-screen').load_screen( "?page=WishListMember&wl=setup/getting-started" , window.parent.document.title );
                history.replaceState(undefined, undefined, "");
                wlm.pushState('wlm3-wizard', '', "?page=WishListMember&wl=setup/getting-started" );
            } else {
                $(".wizard-form").addClass("d-none");
                $(".wizard-form." +step).removeClass("d-none");
            }
        } else {
            $('#the-screen').reload_screen();
        }
    };

    $(".getting-started").find("input[name='expire_option']").click( display_expire_option );

    var ed = $('input[name=expire_date]');
    var xd = moment(ed.val(), WLM3VARS.js_date_format);
    xd = ed.val() ? (xd.isValid() ? xd : moment(ed.val())) : moment().add(7, 'days');
    ed.val(xd.format(WLM3VARS.js_date_format));

    ed.daterangepicker(
        {
            "singleDatePicker": true,
            "showCustomRangeLabel": false,
            "buttonClasses": "btn -default",
            "opens": 'center',
            "applyClass": "-condensed -success",
            "cancelClass": "-condensed -link",
            locale : {
                format: WLM3VARS.js_date_format
            }
            // startDate: new Date(),
        }
    );

    var select = $(".getting-started").find(".wlm-select");
    if ( select.data('select2') ) select.select2('destroy');
    select.select2({theme:"bootstrap"});

    var select = $(".getting-started").find(".integration-wlm-select");
    if ( select.data('select2') ) select.select2('destroy');
    select.select2({theme:"bootstrap", allowClear: true});

    select.on("change", function(e) {
        if ( $(this).val() ) {
            $(this).parent().parent().next().show();
        } else {
            $(this).parent().parent().next().hide();
        }
    });

    //focus on text box
    if ( $(".level-name") ) {
        $(".level-name").focus();
        tmpStr = $(".level-name").val();
        $(".level-name").val('');
        $(".level-name").val(tmpStr);
    }
});


var process_next = function() {
    var $this_button = $(this);
    if ( $this_button.prop("disabled") || $this_button.hasClass("-disable") || $this_button.hasClass("disabled") || $this_button.hasClass("-disabled") ) return false; //if disabled, do nothing
    $('.wlm-toaster').fadeOut(1000);

    if ( $this_button.attr("data-screen") == "start" ) {
        var $lvlid = "";
        if ( typeof $this_button.find("input[name='levelid']") != "undefined" ) $lvlid = $this_button.find("input[name='levelid']").val();
        if ( typeof $lvlid == "undefined" ) $lvlid = "";
        $('#the-screen').load_screen( window.parent.location.href +"&levelid=" +$lvlid , window.parent.document.title );
        wlm.pushState('wlm3-wizard', '', window.parent.location.href +"&levelid=" +$lvlid );
    }else if ( $this_button.attr("next-screen") == "start" ) {
        window.parent.location.href = "?page=WishListMember&wl=setup/getting-started";
    }else if ( $this_button.attr("next-screen") == "home" ) {
        window.parent.location.href = "?page=WishListMember";
    } else {
        $(".wizard-form .level-name-holder").html($(".wizard-form.step-1").find("input[name='name']").val());
        if ( checkfields( $this_button.attr("data-screen") ) ) {
            $(".wizard-form." +$this_button.attr("next-screen")).removeClass("d-none");
            $(".wizard-form." +$this_button.attr("data-screen")).addClass("d-none");
            wlm.pushState('wlm3-wizard', '', "#" +$this_button.attr("next-screen") );
        }
    }
}

var display_expire_option = function() {
    $(this).closest(".row").find(".expire_option .form-inline").hide();
    $(this).closest(".row").find(".expire_option .date-ranger").hide();
    if ( $(this).val() == "1" ) {
        $(this).closest(".row").find(".expire_option").css("padding-top","15px");
        $(this).closest(".row").find(".expire_option .form-inline").show();
    } else if ( $(this).val() == "2" ) {
        $(this).closest(".row").find(".expire_option").css("padding-top","35px");
        $(this).closest(".row").find(".expire_option .date-ranger").show();
    }
}

var checkfields = function( step ) {
    switch( step ) {
        case 'step-1':
            if ( $(".wizard-form.step-1").find("input[name='name']").val() == "" ) {
                $(".wlm-message-holder").show_message({message:wlm.translate( 'Level name is empty' ), type:"danger", icon:"danger"});
                $(".wizard-form.step-1").find("input[name='name']").parent().addClass('has-error');
                return false;
            }

            if ( $levelnames.indexOf($(".wizard-form.step-1").find("input[name='name']").val()) >= 0 ) {
                $(".wlm-message-holder").show_message({message:wlm.translate( 'Level name duplicate' ), type:"danger", icon:"danger"});
                $(".wizard-form.step-1").find("input[name='name']").parent().addClass('has-error');
                return false;
            }
            break;
        default:
            break;
    }
    return true;
}

var process_wizard = function() {
    var $this_button = $(this);
    if ( $this_button.prop("disabled") || $this_button.hasClass("-disable") || $this_button.hasClass("disabled") || $this_button.hasClass("-disabled") ) return false; //if disabled, do nothing

    $('.wlm-toaster').fadeOut(500);

    //this fix issue where leve name shows an error when trying to exit the wizard
    if ( $this_button.hasClass("isexit") ) {
        $('.level-name').removeAttr("required");
    }

    var myicon = $this_button.find(".wlm-icons") ? $this_button.find(".wlm-icons").html() : "";
    var settings_data = $(".getting-started").get_form_data();

    //if new level
    if ( settings_data['levelid'] == "undefined" || !settings_data['levelid'] ) {
        settings_data['levelid'] = parseInt( new Date().getTime() / 1000 );
    }

    $this_button.parent().find(".help-block").addClass("d-none");

    settings_data["action"] = "admin_actions";
    settings_data["WishListMemberAction"] = "process_wizard";
    settings_data["screen"] = $this_button.attr("data-screen");
    settings_data["next"] = $this_button.attr("next-screen");

    var x = $this_button.save_settings({
        data: settings_data,
        on_init: function( $me, $data) {
            $this_button.disable_button({disable:true, icon:"update"});
            $this_button.parent().find(".isback").disable_button({disable:true});
        },
        on_success: function( $me, $result) {
            if ( $result.success ) {
                if ( $result.html != "" ) {
                    $(".getting-started").html($result.html);
                    $(".getting-started").find(".next-btn").click( process_next );
                    history.replaceState(undefined, undefined, "#congrats");
                    //clear history
                    for (i = 0; i < 20; i++) history.pushState({}, '');
                } else {
                    if ( $result.page_to_load ) {
                        window.parent.location.href = $result.page_to_load
                    } else {
                        if ( $result.reload_page ) {
                            window.parent.location.reload(true);
                        } else {
                            $this_button.reload_screen();
                        }
                    }
                }
            } else {
                if ( settings_data["screen"] == "license" ) {
                    $("input[name='license']").parent().addClass("has-error");
                    $this_button.parent().find(".help-block").html($result.msg);
                    $this_button.parent().find(".help-block").removeClass("d-none");
                } else {
                    $(".wlm-message-holder").show_message({message:$result.msg, type:$result.msg_type, icon:$result.msg_type });
                }
            }
        },
        on_fail: function( $me, $data) {
            console.log($data);
            alert(WLM3VARS.request_failed);
            $this_button.disable_button({disable:false, icon: myicon});
            $this_button.parent().find(".isback").disable_button({disable:false, class: '-default'});
        },
        on_error: function( $me, $error_fields) {
            $.each( $error_fields, function( key, obj ) {
                if ( key == "name" ) $(".wlm-message-holder").show_message({message:wlm.translate( 'Level name is empty' ), type:"danger", icon:"danger"});
                if ( typeof obj == "object" ) obj.parent().addClass('has-error');
            });
            $this_button.disable_button({disable:false, icon: myicon});
            $this_button.parent().find(".isback").disable_button({disable:false, class: '-default'});
        },
        on_done: function( $me, $data) {
            $this_button.disable_button({disable:false, icon: myicon});
            $this_button.parent().find(".isback").disable_button({disable:false, class: '-default'});
        }
    });
    return false;
}