(function ($) {

    // настройки со значением по умолчанию
    var defaults = {};

    // публичные методы
    var methods = {

        // инициализация плагина
        init: function (params) {

            // актуальные настройки, будут индивидуальными при каждом запуске
            var options = $.extend({}, defaults, params);

            // инициализируем лишь единожды
            if (!this.data('apiSearchTitle')){

                // закинем настройки в реестр data
                this.data('apiSearchTitle', options);

                //clear_icon
                if ($(options.container_id).find(options.input_id).val())
                    $.fn.apiSearchTitle('showClearIcon',options);


                // далее вся логика
                var tmr;
                var curUrl = '';
                var curSelection = 0;

                $(options.container_id)
                    .on('keyup paste', options.input_id, function (e) {

                        //clear_icon
                        if ($(this).val())
                            $.fn.apiSearchTitle('showClearIcon',options);
                        else
                            $.fn.apiSearchTitle('hideClearIcon',options);

                        var resultRows = $(options.container_id).find(options.result_id).find('.api-item');

                        function api_st_navigate(direction) {
                            if ($(options.container_id).find(options.result_id).find('.api-item-active').size() == 0) {
                                curSelection = -1;
                            }

                            if (direction == 'up' && curSelection != -1) {
                                if (curSelection != 0) {
                                    curSelection--;
                                }
                            } else if (direction == 'down') {
                                if (curSelection != resultRows.size() - 1) {
                                    curSelection++;
                                }
                            }
                            api_st_set_active(curSelection);
                        }

                        function api_st_set_active(index) {
                            resultRows.removeClass('api-item-active');
                            resultRows.eq(index).addClass('api-item-active');
                            curUrl = resultRows.eq(index).find('a').attr("href");
                        }

                        switch (e.keyCode) {
                            case 37: //Left button
                            case 39: //Right button
                            case 32: //Backspace button
                                break;

                            case 27: // escape key - close search div
                                $.fn.apiSearchTitle('hideBackdrop',options);
                                break;

                            case 13: //Enter button
                                if (curUrl != '') {
                                    window.location = curUrl;
                                }
                                break;

                            case 38:
                                api_st_navigate('up');
                                break;

                            case 40:
                                api_st_navigate('down');
                                break;

                            default:
                            {
                                clearTimeout(tmr);
                                tmr = setTimeout(function () {

                                    $(options.container_id).find(options.ajax_icon_id).show();

                                    $.ajax({
                                        type: 'POST',
                                        data: {
                                            sessid: options.sessid,
                                            q: $(options.container_id).find(options.input_id).val(),
                                            API_SEARCH_TITLE_ID: options.component_id,
                                            API_SEARCH_TITLE_AJAX: 'Y'
                                        },
                                        success: function (data) {
                                            $(options.container_id)
                                                .find(options.ajax_icon_id)
                                                .hide();

                                            $(options.container_id)
                                                .find(options.result_id)
                                                .html(data);

                                            /*.find('.api-item-picture')
                                            .each(function(){
                                                var wrapHeight = $(this).closest('.api-item-link').height();
                                                $(this).css({
                                                    'marginTop': '-'+ (wrapHeight/2) +'px'
                                                });
                                                $(this).find('img').css({
                                                    'maxHeight': wrapHeight
                                                });
                                            });*/

                                            $.fn.apiSearchTitle('showScroll',options);

                                            //backdrop
                                            if (options.backdrop.active) {
                                                $.fn.apiSearchTitle('showBackdrop',options);
                                            }
                                        }
                                    });

                                }, options.wait_time);
                            }
                        }
                    })
                    .keydown(function () {
                        clearTimeout(tmr);
                    });

                //backdrop
                if (options.backdrop.active) {
                    $.fn.apiSearchTitle('initBackdrop',options);

                    $(options.container_id)
                        .on('click', options.input_id, function () {
                            if ($(options.container_id).find('.api-category-list').length) {
                                $.fn.apiSearchTitle('showBackdrop',options);
                            }
                        });
                }

                $(window).resize(function () {
                    $.fn.apiSearchTitle('showScroll',options);
                    $.fn.apiSearchTitle('checkWidth',options);
                });

                $.fn.apiSearchTitle('checkWidth',options);

                //clear_icon
                $(options.container_id)
                    .on('click', options.clear_icon_id, function () {
                        $(options.container_id).find(options.input_id).val('');
                        $(options.container_id).find(options.result_id).html('');
                        $.fn.apiSearchTitle('hideBackdrop',options);
                        $(this).hide();
                    });
            }

            return this;
        },
        initBackdrop: function (options) {
            var backdrop = $("<div/>", {
                "id": options.backdrop.id,
                "class": options.backdrop.clas,
                "css": options.backdrop.css,
                "click": function () {
                    $.fn.apiSearchTitle('hideBackdrop',options);
                }
            }).appendTo('body');

            backdrop.hide();
        },
        hideBackdrop: function (options) {
            $('#'+options.backdrop.id).fadeOut(0);
            $(options.parent_id).removeClass('api-backdrop-active').removeAttr('style');
            /*$(options.container_id).find(options.result_id).fadeOut(0);*/
            $(options.container_id).find(options.scroll_id).css({'visibility': 'hidden'});
        },
        showBackdrop: function (options) {
            $('#'+options.backdrop.id).fadeIn(0);
            $(options.parent_id).addClass('api-backdrop-active').css(options.parent.css);
            /*$(options.container_id).find(options.result_id).fadeIn(0);*/

            if(!$(options.container_id).find('.api-category-list').length)
                $(options.container_id).find(options.scroll_id).css({'visibility': 'hidden'});
            else
                $(options.container_id).find(options.scroll_id).css({'visibility': 'visible'});
        },
        showScroll: function (options) {

            var search_result_obj = $(options.container_id).find(options.result_id);
            if (search_result_obj.length) {
                var offset_top = search_result_obj.offset().top || $(options.container_id).offset().top;

                search_result_obj.css({
                    "max-height": ($(window).height() - (offset_top+30))
                });
            }
        },
        showClearIcon: function (options) {
            $(options.container_id).find(options.clear_icon_id).fadeIn(200);
        },
        hideClearIcon: function (options) {
            $(options.container_id).find(options.clear_icon_id).hide();
        },
        checkWidth: function(options){
            if($(options.container_id).width() <= 320)
            {
                $(options.container_id).addClass('api-width-mini');
            }
            else
                $(options.container_id).removeClass('api-width-mini');
        }
    };

    $.fn.apiSearchTitle = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Error! Method "' + method + '" not found in plugin $.fn.apiSearchTitle');
        }
    };

})(jQuery);

(function($){
// настройки со значением по умолчанию
    var defaults = {};

    // публичные методы
    var methods = {

        // инициализация плагина
        init: function (params) {

            // актуальные настройки, будут индивидуальными при каждом запуске
            var options = $.extend({}, defaults, params);
            console.log(options);

            console.log($.fn.TWB_CatalogSticker('getProductItems',options));

            return this;
        },
        show:function(options){
            console.log(options);
        },
        getProductItems:function (options) {
            var items=$(options.PRODUCT_ITEM_SELECTOR);
            var result={};

            if(items.length>0){
                $.each(items , function(index, item) {
                    index++;
                    result[index]=item;
                });
                return result;
            }

            return;
        }
    };

    $.fn.TWB_CatalogSticker = function (method) {
        // логика вызова метода
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Метод ' +  method + ' в jQuery.TWB_CatalogSticker не существует' );
        }
    };

/*
    function TWB_GetCatalogItems(){
        var items=$('[data-entity="item"]');
        var result={};

        if(items.length>0){
            $.each(items , function(index, item) {
                index++;
                result[index]=item;
            });
            return result;
        }

        return;
    }

    function TWB_InsertSticker(item_product,sticker){
        console.log('TWB_InsertSticker');
        var product=item_product.parent( "div" )[0];
        console.log(product);
        console.log(sticker);
        var sticker_clone=$(sticker).clone();
        //product.before(sticker_clone);
        $(sticker).clone().insertBefore($(product));
    }

    $.fn.TWB_CatalogSticker2 = function(params){
        var product_items;
        var cnt_product_items;
        var item=[];

        console.log(params);


        product_items=TWB_GetCatalogItems();
        cnt_product_items=product_items.length;

        console.log(product_items);
        $.each(params.ITEMS_ID , function(index, val) {
            var el=$('#'+val);

            if(el[0])
                item.push(el[0]);
        });

        if(params.TYPE == 'POSITIONS'){

            $.each(product_items , function(index_product, item_product) {
                var cath=false;

                cath=$.each(params.TYPE_OPTIONS , function(index_position, val_position) {
                    val_position=parseInt(val_position);


                    if(index_product==val_position){
                        console.log('Совпали позиции');
                        console.log();
                        //console.log('Позиция товара '+index_product);
                        //console.log('Позиция стикера '+val_position);

                        TWB_InsertSticker($(item_product),item[0]);

                        return true;
                    }
                });


            });

        }

        console.log(item);

        return this;
    }*/

})(jQuery);
