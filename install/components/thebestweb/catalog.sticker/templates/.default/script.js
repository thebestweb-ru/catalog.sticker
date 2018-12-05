(function($){
// настройки со значением по умолчанию
    var defaults = {};

    // публичные методы
    var methods = {

        // инициализация плагина
        init: function (params) {

            // актуальные настройки, будут индивидуальными при каждом запуске
            var options = $.extend({}, defaults, params);
            var container;

            options.product_rows = $.fn.TWB_CatalogSticker('getProductRow',options);
            options.container_node =$.fn.TWB_CatalogSticker('getContainer',options);
            options.sticker_items=$.fn.TWB_CatalogSticker('getStickerItems',options);
            options.product_items=$.fn.TWB_CatalogSticker('getProductItems',options);
            if(!options.container_node || options.sticker_items.length<0)
                return false;


            //console.log(options.container_node);

            // создаем новый экземпляр наблюдателя
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if(mutation.target!==options.container_node)
                        return false;

                    options.product_items=$.fn.TWB_CatalogSticker('getProductItems',options);
                    options.product_rows = $.fn.TWB_CatalogSticker('getProductRow',options);
                    $.fn.TWB_CatalogSticker('setSticker',options);
                });
            });

            // создаем конфигурации для наблюдателя
            var config = { childList: true, subtree: true };

            // запускаем механизм наблюдения
            observer.observe(options.container_node,  config);

            $.fn.TWB_CatalogSticker('setSticker',options);

            // позже, если надо, прекращаем наблюдение
            //observer.disconnect();

            return this;
        },
        setSticker:function(options){
            console.log('setSticker');
            switch (options.TYPE){

                case "FIXED":
                    $.fn.TWB_CatalogSticker('setFixed',options);
                    break;
                case "POSITIONS":
                    $.fn.TWB_CatalogSticker('setPositions',options);
                    break;
                case "FIXED_POSITIONS":
                    $.fn.TWB_CatalogSticker('setFixedPositions',options);
                    break;
            }
            console.log(options);
            return this;
        },
        getStickerItems:function(options){
            var item=[];
            $.each(options.ITEMS_ID , function(index, val) {
                var el=$('#'+val);

                item.push(el[0]);
            });
            return item;
        },
        getProductItems:function (options) {
            var items=$(''+options.PRODUCT_ITEM_SELECTOR+','+options.STICKER_SELECTOR,options.container_node);
            var result={};

            if(items.length>0){
                $.each(items , function(index, item) {
                    index++;
                    result[index]=item;
                });
                return result;
            }

            return;
        },
        getProductRow:function (options) {
            var rows=$(options.PRODUCT_ROW_SELECTOR);
            if(rows.length>0)
                return rows;

            return false;
        },
        getContainer:function (options) {
           var container;

            if(options.product_rows!=false && options.product_rows.length>0){
                container=options.product_rows.parent(options.PRODUCT_CONTAINER_SELECTOR);
            }

            if(container.length>0)
                return container[0];

            container=$(options.PRODUCT_CONTAINER_SELECTOR);

            if(container.length>0)
                return container[0];

            return false;

        },
        setFixed:function (options) {
            var val_position=parseInt(options.TYPE_OPTIONS);

            if ($(options.product_items[val_position]).data('entity') == 'sticker') {
                return;
            }

            options.set_product_item=options.product_items[val_position];
            options.set_sticker = $.fn.TWB_CatalogSticker('getStickerItem',options);
            $.fn.TWB_CatalogSticker('setStickerItem',options);
            return this;
        },
        setPositions:function(options){
            $.each(options.TYPE_OPTIONS , function(index_pos, value) {
                var val_position = parseInt(value);

                if ($(options.product_items[val_position]).data('entity') == 'sticker') {
                    return;
                }

                options.set_product_item = options.product_items[val_position];
                options.set_sticker = $.fn.TWB_CatalogSticker('getStickerItem', options);
                $.fn.TWB_CatalogSticker('setStickerItem', options);
            });
            return this;
        },
        setFixedPositions:function(options){
            var val_position=parseInt(options.TYPE_OPTIONS);
            var current_pos=0;

            $.each(options.product_items , function(index_product, item_product) {
                current_pos++;

                if($(item_product).data('entity')=='sticker'){
                    current_pos=0;
                }

                if(current_pos==val_position){
                    options.set_product_item=item_product;
                    options.set_sticker = $.fn.TWB_CatalogSticker('getStickerItem',options);
                    $.fn.TWB_CatalogSticker('setStickerItem',options);
                    current_pos=1;
                }
            });
            return this;
        },
        setStickerItem:function (options) {
            var product_node=$(options.set_product_item).parent( "div" )[0];
            var sticker_clone=$(options.sticker_items[options.set_sticker]).clone();
            $(sticker_clone).insertBefore($(product_node));
            options.product_items=$.fn.TWB_CatalogSticker('getProductItems',options);
            return this;
        },
        getStickerItem:function (options) {
            var i = 0;
            if(options.last_sticker!==undefined){
                i = options.last_sticker;
            }else{
                options.last_sticker=i;
                return i;
            }
            i++;

            if(i<=options.sticker_items.length){
                options.last_sticker=i;
                return i;
            }else{
                options.last_sticker=undefined;
                return 0;
            }

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

})(jQuery);
