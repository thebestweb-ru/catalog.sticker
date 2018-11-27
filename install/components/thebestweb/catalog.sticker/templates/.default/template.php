<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(method_exists($this, 'setFrameMode'))
    $this->setFrameMode(true);

$this->addExternalJs('/bitrix/js/oceandevelop/wishlist/wishlist.js');
//$APPLICATION->AddHeadScript('/bitrix/js/oceandevelop/wishlist.js');
//CJSCore::Init(array('ajax'));

$JSParams=array(
    'AJAX_ID'=>$arParams['AJAX_ID'],
    'LIST_URL'=>$arParams['LIST_URL'],
);

$mainId = $this->GetEditAreaId('wishlist_main');
$bxajaxid = $arParams['AJAX_ID'];//CAjax::GetComponentID('oceandevelop:wishlist.main', $templateName);
$signer = new \Bitrix\Main\Security\Sign\Signer;
$signedTemplate = $signer->sign($templateName, 'wishlist.main');
$signedParams = $signer->sign(base64_encode(serialize($JSParams)), 'wishlist.main');

//debugmessage($arParams);
?>
<div id="comp_<?=$bxajaxid?>">
    <div id="<?=$mainId?>" class="od_wishlist_wrap">
        <a href="<?=$arParams['LIST_URL']?>" class="wishlist-list-wrap <?=!empty($arResult['ITEMS']) ?'notempty':''?>">
            <div>
                <span class="wishlist-count-empty"><i class="fal fa-heart"></i></span>
            </div>
            <div>
                <span class="wishlist-count-notempty"><i class="fas fa-heart"></i></span>
            </div>
            <div>(<span class="wishlist-counter"><?=count($arResult['ITEMS'])?></span>)</div>
        </a>
    </div>
    <script>
        $(function(){
            WishlistProJsInit();
            $('#comp_<?=CUtil::JSEscape($bxajaxid)?>').WishlistPro({
                counter:{
                    wrapper:'#comp_<?=CUtil::JSEscape($bxajaxid)?>',
                    ajaxURL: '<?=CUtil::JSEscape($componentPath)?>/ajax.php',
                    signedTemplate:'<?=CUtil::JSEscape($signedTemplate)?>',
                    signedParams:'<?=CUtil::JSEscape($signedParams)?>',
                    bxajaxid: '<?=CUtil::JSEscape($bxajaxid)?>',
                    siteId: '<?=CUtil::JSEscape(SITE_ID)?>',
                },
                items: <?=CUtil::PhpToJSObject($arResult['ITEMS'])?>,
                cnt_items: <?=CUtil::JSEscape(count($arResult['ITEMS']))?>,
            });
        });
    </script>
</div>
