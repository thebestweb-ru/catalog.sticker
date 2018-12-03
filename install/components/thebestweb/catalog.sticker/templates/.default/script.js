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
            options.set_sticker;
            options.last_sticker;
            options.product_items=$.fn.TWB_CatalogSticker('getProductItems',options);
            options.set_product_item;
            if(!options.container_node || options.sticker_items.length<0)
                return false;


            //console.log(options.container_node);

            // создаем новый экземпляр наблюдателя
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if(mutation.target!==options.container_node)
                        return false;

                    console.log(mutation.type);
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

            /*//$('.catalog-section').on("change",$.fn.TWB_CatalogSticker('updateContainer',options));
            console.log(this);
            console.log(options);
            //console.log($.fn.TWB_CatalogSticker('getProductItems',options));*/

            return this;
        },
        setSticker:function(options){
            console.log('setSticker');
            console.log(options);

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
            console.log('setFixed');
            var val_position=parseInt(options.TYPE_OPTIONS);

            $.each(options.product_items , function(index_product, item_product) {

                if($(item_product).data('entity')=='sticker'){
                    return false;
                }

                if(index_product==val_position){
                    options.set_product_item=item_product;
                    options.set_sticker = $.fn.TWB_CatalogSticker('getStickerItem',options);
                    $.fn.TWB_CatalogSticker('setStickerItem',options);
                    return false;
                }
            });
        },
        setPositions:function(options){
            console.log('setPositions');


        },
        setFixedPositions:function(options){
            console.log('setFixedPositions');
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
        },
        setStickerItem:function (options) {
            var product_node=$(options.set_product_item).parent( "div" )[0];
            var sticker_clone=$(options.set_sticker).clone();
            $(sticker_clone).insertBefore($(product_node));
            return true;
        },
        getStickerItem:function (options) {

            if(!options.last_sticker){
                options.last_sticker=options.sticker_items[0];
                return options.sticker_items[0];
            }


            if(options.sticker_items.length<2){
                options.last_sticker=options.sticker_items[0];
                return options.sticker_items[0];
            }

            for (var i = 0; i < options.sticker_items.length; i++) {
                if(options.sticker_items[i]!==options.last_sticker){
                    options.last_sticker=options.sticker_items[i];
                    return options.sticker_items[i];
                }
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
