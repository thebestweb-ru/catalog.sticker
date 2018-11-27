(function (w) {
    if (w.$){
        w.WishlistProJsInit = function () {
        }
        return;
    }
    var _funcs = [];
    w.$ = function (f) { // add functions to a queue
        _funcs.push(f);
    };
    w.WishlistProJsInit = function () { // move the queue to jQuery's DOMReady
        while (f = _funcs.shift())
            f();
    };
})(window);
$(function() {
    $.WishlistProInherit = function (name, base, prototype) {

        if (!prototype) {
            prototype = base;
            base = $.WishlistPro;
        }

        $[name] = function (wrapper, settings) {
            if (arguments.length) {
                this.init(wrapper, settings);
            }
        };

        var basePrototype = new base();
        basePrototype.options = $.extend({}, basePrototype.options);
        $[name].prototype = $.extend(true, basePrototype, {name: name}, prototype);

        $.fn[name] = function (options) {
            var filters = [];
            $(this).each(function () {
                var filter = new $[name](this, options);
                filters[filters.length] = filter;
            });
            return filters;
        };
    };
    $.WishlistPro = function (wrapper, options) {
        if (arguments.length) {
            this.init(wrapper, options);
        }
        var _this = this;
        $( document ).on( 'click', 'a[data-wishlist-el]', function(e){
            e.preventDefault();
            var el = $(this), element_id = $(this).data('wishlist-el');
            _this.ItemUpdateLinks(el,element_id);
        });
        $( document ).on( 'click', '.wishlist_pro_close_panel',function(e){
            e.preventDefault();
            _this.HideInformer();
        });

    };
    $.WishlistPro.prototype = {
        options: {
            counter: null,
            informer: null,
            form:null,
            list:null,
            item: null,
            error:'',
            message:'',
            action:'',
            cnt_items:0
        },

        init: function (wrapper, options) {
            this.wrapper = $(wrapper);
            this.options = $.extend(this.options, options);

            //console.log('INIT');
            //console.log(this);

        },
        initCatalogItems:function(){

            if(this.options.items){
                var all_items=$('a[data-wishlist-el]',document);
                var _this=this;

                $.each(all_items, function (index, element) {
                    var el_id=$(element).data('wishlist-el');
                    if(!el_id)
                        return;

                    var result=false;


                    $.each(_this.options.items, function (index, value) {
                        var id=value.ELEMENT_ID;
                        if(!id){
                            result=true;
                            return true;
                        }

                        if(el_id==id){
                            $(element).addClass('wishlist-added');
                            result=true;
                            return true;
                        }
                    });

                    if(!result)
                        $(element).removeClass('wishlist-added');
                });
            }
        },
        ItemUpdateLinks: function(el,element_id) {
           // console.log('ItemUpdateLinks');

            if(!element_id || this.options.counter.length)
                return false;

            var counter=this.options.counter;

            var _this = this;

            $.ajax({
                type: 'POST',
                url: counter.ajaxURL,
                dataType: "json",
                data: {
                    ajax: 'Y',
                    bxajaxid: counter.bxajaxid,
                    signedParams: counter.signedParams,
                    signedTemplate: counter.signedTemplate,
                    siteId: counter.siteId,
                    action: 'update_item',
                    element_id: element_id,
                },
                success: function (data) {
                    //console.log(data);

                    if (!data.RESULT) {
                        _this.options.error = 'WishlistPro. Request not JSON';
                    }

                    if (data.ERROR)
                        _this.options.error = data.ERROR;

                    el.toggleClass('wishlist-added');

                    if (data.ACTION) {
                        _this.options.action = data.ACTION;
                    }
                    if (data.ITEM)
                        _this.options.item = data.ITEM;

                    if (data.ITEMS)
                        _this.options.items = data.ITEMS;

                    _this.options.cnt_items = data.CNT_ITEMS;

                    if (data.MESSAGE)
                        _this.options.message = data.MESSAGE;

                },
                complete:function(){
                    //console.log('afterAjax');
                    //console.log(_this);
                    _this.UpdateCounter(_this);
                    _this.InitInformer();
                    _this.InitList();

                },
                error: function(request,error) {

                }
            });

        },
        UpdateCounter:function(_this){

            if(!this.options.counter)
                return false;

            var span=$(this.options.counter.wrapper).find('.wishlist-counter');
            if(span)
                span.html(this.options.cnt_items);

        },
        InitInformer:function(){

            if(!this.options.informer)
                return false;

            //console.log('InitInformer');
            //console.log(this);
            var informer=this.options.informer;

            this.UpdateInformer(informer);

        },
        InitList:function(){

            if(!this.options.list)
                return false;

            //console.log('InitList');
            //console.log(this);

            var list=this.options.list;
            var wrapper=$(list.wrapper);

            var data={
                ajax: 'Y',
                bxajaxid: list.bxajaxid,
                signedParams: list.signedParams,
                signedTemplate: list.signedTemplate,
                siteId: list.siteId,
            };

            $.ajax({
                type: 'GET',
                url: list.ajaxURL,
                dataType: "html",
                data: data,
                beforeSend: function () {
                    BX.showWait(BX(list.wrapper));
                },
                success: function (data) {
                    //console.log(data);
                    wrapper.html($(data));
                },
                complete:function(){
                    BX.closeWait(BX(list.wrapper));
                    return true;
                },
                error: function(request,error) {

                }
            });

        },
        InitForm:function(){

            if(!this.options.form)
                return false;

            var form=this.options.form;
            var wrapper=$(form.wrapper);
            if(!wrapper)
                return false;

            var _this = this;

            $('form',wrapper).one('submit', function (e) {
                e.preventDefault();
               /* console.log('Отправка формы');
                console.log($( this ));
                $( this ).trigger( "testsubmit" );

                /*if (!$.UserConsent)
                {
                    return;
                }*/
               /* $(document).on('main-user-consent-request-accepted',
                    function (form) {
                        console.log('js event:', 'save',form);
                        // успешно!
                        // отправляем ajax
                        // или другие действия
                    });
                $( this ).on('main-user-consent-request-accepted',
                    function (form) {
                        console.log('js event:', 'save',form);
                        // успешно!
                        // отправляем ajax
                        // или другие действия
                    });

                console.log('submit');
                return false;*/
                /*BX.onCustomEvent('my-event-name', []);
                //console.log(_this);
                if (!BX.UserConsent)
                {
                    return;
                }
                var control = BX.UserConsent.load(BX(document.getElementById('comp_'+form.bxajaxid).querySelector('form')));
                //console.log(form.wrapper);
                //console.log(document.getElementById('comp_'+form.bxajaxid));
                //console.log(document.getElementById('comp_'+form.bxajaxid).querySelector('form'));
                console.log(control);
                if (!control)
                {
                    return;
                }

                BX.addCustomEvent(
                    control,
                    BX.UserConsent.events.save,
                    function (data) {
                        console.log('js event:', 'save', data);
                        // успешно!
                        // отправляем ajax
                        // или другие действия
                    }
                );*/
                var data={
                    ajax: 'Y',
                    bxajaxid: form.bxajaxid,
                    signedParams: form.signedParams,
                    signedTemplate: form.signedTemplate,
                    siteId: form.siteId,
                    action:'send',
                };
                data['sessid']=wrapper.find('[name="sessid"]').val();
                data['wishlist_code']=wrapper.find('[name="wishlist_code"]').val();
                data['user_name']=wrapper.find('[name="user_name"]').val();
                data['user_email']=wrapper.find('[name="user_email"]').val();
                data['user_phone']=wrapper.find('[name="user_phone"]').val();

                $.ajax({
                    type: 'POST',
                    url: form.ajaxURL,
                    data: data,
                    success: function (data) {
                        //console.log(data);
                        $(form.wrapper).html($(data).find('#wishlist_form'));
                    },
                    complete:function(){

                        return true;
                    },
                    error: function(request,error) {

                    }
                });
            });

            if(form.arResult.SEND_FORM){
                if(form.JSParams.HIDE_AFTER_SEND=='Y')
                    setTimeout(function(){  _this.HideInformer(); }, 5000);

                if(form.JSParams.REDIRECT_AFTER_SEND.length>0){
                    setTimeout(function(){location.replace(form.JSParams.REDIRECT_AFTER_SEND)},5000);
                }
            }
        },
        UpdateInformer:function(informer){
            //console.log('UpdateInformer');

            var _this = this;

            var data={
                ajax: 'Y',
                bxajaxid: informer.bxajaxid,
                signedParams: informer.signedParams,
                signedTemplate: informer.signedTemplate,
                siteId: informer.siteId,
            };

            if(this.options.action)
                data['action']=this.options.action;

            if(this.options.item)
                data['element_id']=this.options.item.ID;

            $.ajax({
                type: 'GET',
                url: informer.ajaxURL,
                dataType: "html",
                data: data,
                success: function (data) {
                    //console.log(data);
                    $(informer.wrapper).html($(data).find('#wishlist_pro_informer'));
                },
                complete:function(){

                    if(informer.JSParams.SHOW_AFTER_UPDATE=='Y'){
                        _this.ShowHideInformer();
                    }

                    return true;
                },
                error: function(request,error) {

                }
            });


        },
        ShowHideInformer:function(){
            //console.log('ShowHideInformer');
            if(!this.options.informer)
                return false;

            var informer=this.options.informer;
            $(informer.wrapper).find('#wishlist_pro_informer').toggleClass('show-panel');
        },
        HideInformer:function(){
            //console.log('ShowHideInformer');
            if(!this.options.informer)
                return false;

            var informer=this.options.informer;
            $(informer.wrapper).find('#wishlist_pro_informer').removeClass('show-panel');
        },
    };

    $.WishlistProInherit('WishlistPro');
});