(function($){

   /* // публичные методы
    var methods = {

    };

    $.fn.apiSearchTitle = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Error! Method "' + method + '" not found in plugin $.fn.apiSearchTitle');
        }
    };*/



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

    $.fn.TWB_CatalogSticker = function(params){
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
    }
})(jQuery);
