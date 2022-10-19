var paypal_common = {
    pp_currencies: {
        USD: {
            value: 'USD',
            text: 'U.S. Dollar'
        },
        AUD: {
            value: 'AUD',
            text: wlm.translate( 'Australian Dollar' )
        },
        BRL: {
            value: 'BRL',
            text: wlm.translate( 'Brazilian Real' )
        },
        CAD: {
            value: 'CAD',
            text: wlm.translate( 'Canadian Dollar' )
        },
        CZK: {
            value: 'CZK',
            text: wlm.translate( 'Czech Koruna' )
        },
        DKK: {
            value: 'DKK',
            text: wlm.translate( 'Danish Krone' )
        },
        EUR: {
            value: 'EUR',
            text: wlm.translate( 'Euro' )
        },
        HKD: {
            value: 'HKD',
            text: wlm.translate( 'Hong Kong Dollar' )
        },
        HUF: {
            value: 'HUF',
            text: wlm.translate( 'Hungarian Forint' )
        },
        ILS: {
            value: 'ILS',
            text: wlm.translate( 'Israeli New Sheqel' )
        },
        INR: {
            value: 'INR',
            text: wlm.translate( 'Indian Rupee' )
        },
        JPY: {
            value: 'JPY',
            text: wlm.translate( 'Japanese Yen' )
        },
        MYR: {
            value: 'MYR',
            text: wlm.translate( 'Malaysian Ringgit' )
        },
        MXN: {
            value: 'MXN',
            text: wlm.translate( 'Mexican Peso' )
        },
        NOK: {
            value: 'NOK',
            text: wlm.translate( 'Norwegian Krone' )
        },
        NZD: {
            value: 'NZD',
            text: wlm.translate( 'New Zealand Dollar' )
        },
        PHP: {
            value: 'PHP',
            text: wlm.translate( 'Philippine Peso' )
        },
        PLN: {
            value: 'PLN',
            text: wlm.translate( 'Polish Zloty' )
        },
        GBP: {
            value: 'GBP',
            text: wlm.translate( 'Pound Sterling' )
        },
        RUB: {
            value: 'RUB',
            text: wlm.translate( 'Russian Ruble' )
        },
        SGD: {
            value: 'SGD',
            text: wlm.translate( 'Singapore Dollar' )
        },
        SEK: {
            value: 'SEK',
            text: wlm.translate( 'Swedish Krona' )
        },
        CHF: {
            value: 'CHF',
            text: wlm.translate( 'Swiss Franc' )
        },
        TWD: {
            value: 'TWD',
            text: wlm.translate( 'Taiwan New Dollar' )
        },
        THB: {
            value: 'THB',
            text: wlm.translate( 'Thai Baht' )
        }
    },
    pp_billing_cycle: [{
        value: wlm.translate( 'Day' ),
        text: wlm.translate( 'Day(s)' )
    }, {
        value: wlm.translate( 'Week' ),
        text: wlm.translate( 'Week(s)' )
    }, {
        value: wlm.translate( 'Month' ),
        text: wlm.translate( 'Month(s)' )
    }, {
        value: wlm.translate( 'Year' ),
        text: wlm.translate( 'Year(s)' )
    }],
    payflow_billing_cycle: [{
        value: "DAY",
        text: wlm.translate( 'Day(s)' )
    }, {
        value: "WEEK",
        text: wlm.translate( 'Weekly' )
    }, {
        value: "BIWK",
        text: wlm.translate( 'Every Two Weeks' )
    }, {
        value: "SMMO",
        text: wlm.translate( 'Twice Every Month' )
    }, {
        value: "FRWK",
        text: wlm.translate( 'Every Four Weeks' )
    }, {
        value: "MONT",
        text: wlm.translate( 'Monthly' )
    }, {
        value: "QTER",
        text: wlm.translate( 'Quarterly' )
    }, {
        value: "SMYR",
        text: wlm.translate( 'Twice Every Year' )
    }, {
        value: "YEAR",
        text: wlm.translate( 'Yearly' )
    }],
    levels_select_group: [],
    pp_stop_after: [],

    prefix : '',

    fxn : {
        // settings tab
        settings_handlers : function (obj) {
            $(obj).off('change.wlm3', '#' + paypal_common.prefix + '-enable-sandbox');
            $(obj).on('change.wlm3', '#' + paypal_common.prefix + '-enable-sandbox', function() {
                if ($(this).is(':checked')) {
                    $('#' + paypal_common.prefix + '-sandbox-settings').slideDown(300);
                } else {
                    $('#' + paypal_common.prefix + '-sandbox-settings').slideUp(300);
                }
            });
            $('#' + paypal_common.prefix + '-enable-sandbox').trigger('change.wlm3');
        },
        cleantbody : function() {
            if ($('#' + paypal_common.prefix + '-products tbody').find('tr').length < 1) {
                $('#' + paypal_common.prefix + '-products tbody').html('').text('');
            }
        },
        load_products : function (products, append) {
            if(typeof products == 'object') {
                var tmpl = _.template($('script#' + paypal_common.prefix + '-products-template').html(), {
                    variable: 'data'
                });
                var html = tmpl(products);
                if(!append) $('#' + paypal_common.prefix + '-products tbody').empty()
                $('#' + paypal_common.prefix + '-products tbody').append(html);

                $( '#' + paypal_common.prefix + '-products .-del-btn' ).do_confirm( { confirm_message : wlm.translate( 'Delete this Product?' ), yes_button : wlm.translate( 'Delete' ) } ).on( 'yes.do_confirm', function() {
                    var pid = $(this).closest('tr').data('id');
                    var edit = $('#' + paypal_common.prefix + '_edit_product_' + pid);
                    edit.remove();

                    var tbody = $(this).closest('tbody');
                    $(this).closest('tr').fadeOut(300, function() {
                        $(this).remove();
                        paypal_common.fxn.cleantbody();
                    });

                    $.post( WLM3VARS.ajaxurl, {
                        action : paypal_common.prefix + '_delete_product',
                        id : pid
                    } );

                    delete paypal_common.products[pid];
                    return false;
                } );

                // generate modals
                if(!$('body').hasClass('modal-open')) {
                    var tmpl = _.template($('script#' + paypal_common.prefix + '-products-edit-template').html(), {
                        variable: 'data'
                    });
                    var html = tmpl(products);
                    if(!append) $('#' + paypal_common.prefix + '-products-edit').empty();
                    $('#' + paypal_common.prefix + '-products-edit').append(html);

                    $('#' + paypal_common.prefix + '-products-edit [data-process="modal"]').each(function() {
                        new wlm3_modal(
                            '#' + $(this)[0].id
                        );
                    });
                }
            }
            paypal_common.fxn.cleantbody();
        },
        edit_form : function(id) {
            var modal = $('#' + paypal_common.prefix + '_edit_product_' + id);

            modal.transformers();

            var data = {};
            data[paypal_common.products_option] = paypal_common.products;
            modal.set_form_data(data);

            modal.find('.-paypal-recurring-toggle, .-paypal-trial1-toggle, .-paypal-trial2-toggle').change();
            modal.modal('show');
        },
        products_handlers : function (obj) {

            // toggle recurring
            $(obj).on('change.wlm3', '.-paypal-recurring-toggle', function() {
                if(!this.checked) return;
                if (this.value == '1') {
                    $(this).closest('.' + paypal_common.prefix + '-product-form').addClass('-is-recurring').removeClass('-is-onetime');
                } else {
                    $(this).closest('.' + paypal_common.prefix + '-product-form').addClass('-is-onetime').removeClass('-is-recurring');
                }
            });
            // toggle trial
            $(obj).on('change.wlm3', '.-paypal-trial1-toggle', function() {
                var checked = $(this).prop('checked');
                if (checked) {
                    $(this).closest('.' + paypal_common.prefix + '-product-form').addClass('-has-trial1');
                } else {
                    $(this).closest('.' + paypal_common.prefix + '-product-form').removeClass('-has-trial1');
                }
            });
            // toggle trial2
            $(obj).on('change.wlm3', '.-paypal-trial2-toggle', function() {
                var checked = $(this).prop('checked');
                if (checked) {
                    $(this).closest('.' + paypal_common.prefix + '-product-form').addClass('-has-trial2');
                } else {
                    $(this).closest('.' + paypal_common.prefix + '-product-form').removeClass('-has-trial2');
                }
            });
            
            $('#' + paypal_common.prefix + '-products')
            .off('click.wlm3', '.-add-btn') // add new button
            .on('click.wlm3', '.-add-btn', function() {
                var _id = (Date.now() + 1).toString(36).toUpperCase();
                var data = {}
                data[_id] = $.extend(
                    {
                        id: _id,
                        new_product: true,
                        name: wlm.translate( 'Product #' ) + parseInt(window.performance.now()),
                    },
                    paypal_common.new_product
                );

                $.extend(paypal_common.products, data);
                paypal_common.fxn.load_products(data, true);

                paypal_common.fxn.edit_form(_id);
                return false;
            })
            .off('click.wlm3', '.-edit-btn') // edit button
            .on('click.wlm3', '.-edit-btn', function() {
                paypal_common.fxn.edit_form($(this).closest('tr').data('id'));
                return false;
            })
            .off('click.wlm3', '.paypal-copy-form')
            .on('click.wlm3', '.paypal-copy-form', function() {
                if(!$(this).data('text')) {
                    var _this = this;
                    $(_this).data('text', 'loading...');
                    $.post(WLM3VARS.ajaxurl, {
                        action: 'wlm_paypalps_get-product-form',
                        product_id: $(this).data('id')
                    }, function(r) {
                        $(_this).data('text', r);
                        $(_this).popover('show');
                    });
                }
            });
        },
        after_modal_save : function(me, settings_data, jqXHR, textStatus) {
            var data = {};

            $.each(settings_data, function(index, value) {
                try {
                    var x = index.match(/\[([^\[\]]+?)\]$/)[1];
                    data[x] = value;
                } catch(e) {}
            });

            paypal_common.products[data.id] = data;
            paypal_common.fxn.load_products(paypal_common.products);

        },
        init : function(obj) {
            paypal_common.fxn.settings_handlers(obj);
            paypal_common.fxn.cleantbody();
            paypal_common.fxn.load_products(paypal_common.products, false);
            paypal_common.fxn.products_handlers(obj);
        }
    }
}

$.each(all_levels, function(group, levels) {
    var group = {
        name: post_types[group].labels.name,
        options: []
    };
    $.each(levels, function(level_id, level) {
        group.options.push({
            value: level.id,
            text: level.name
        });
    });
    paypal_common.levels_select_group.push(group);
});

var cycle = wlm.translate( ' cycle' );
for (var i = 1; i <= 52; i++) {
    paypal_common.pp_stop_after.push({
        value: i,
        text: (i < 2 ? wlm.translate( 'Never' ) : i + cycle)
    });
    cycle = wlm.translate( ' cycles' );
}
