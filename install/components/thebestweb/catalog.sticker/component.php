<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Loader;

$this->setFrameMode(false);

if (!CModule::includeModule('oceandevelop.wishlist'))
{
    ShowError(GetMessage("WISHLIST_ADD_NOT_INSTALL"));
    return false;
}

if (!CModule::includeModule('iblock'))
{
    ShowError(GetMessage("WISHLIST_ADD_NOT_INSTALL"));
    return false;
}
if(!isset($arParams['AJAX_ID']))
    $arParams['AJAX_ID']=CAjax::GetComponentID($this->__name, $this->__templateName);

$arResult=array();

$wishlist = new ODWishlist();


$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new \Bitrix\Main\Web\PostDecodeFilter);

if($request->isPost() && $request['bxajaxid']== $arParams['AJAX_ID']){
    if (isset($_GET['bxajaxid']))
    {
        if(is_array($_GET['bxajaxid']))
            $arResult['COMPONENT_CONTAINER_ID'] = htmlspecialcharsbx('comp_'.$_GET['bxajaxid'][0]);
        else
            $arResult['COMPONENT_CONTAINER_ID'] = htmlspecialcharsbx('comp_'.$_GET['bxajaxid']);
    }

    if(isset($request['action'])){
        switch ($request['action']){
            case 'update_item':
                $IBLOCK_ID=CIBlockElement::GetIBlockByID( intval($request["element_id"]));
                if($IBLOCK_ID){

                    $arItem=array(
                        "IBLOCK_ID"=> $IBLOCK_ID,
                        "ELEMENT_ID"=> intval($request["element_id"]),
                    );
                    $res=$wishlist->updateWishlistItem($arItem);
                    if(!$wishlist->getErrors()){
                        $arResult['RESULT']=true;
                        $arResult['MESSAGE']=$wishlist->getMessages();
                        $arResult['ACTION']=$wishlist->getCurrentItemAction();

                        $res = CIBlockElement::GetByID($arItem["ELEMENT_ID"]);
                        if($item = $res->GetNext(true,false)){
                            if (!is_array($item['PREVIEW_PICTURE']))
                                $item['PREVIEW_PICTURE'] = CFile::GetFileArray($item['PREVIEW_PICTURE']);
                            if (!is_array($item['DETAIL_PICTURE']))
                                $item['DETAIL_PICTURE'] = CFile::GetFileArray($item['DETAIL_PICTURE']);
                            $arResult['ITEM']=$item;
                        }
                        $arResult['ITEMS'] = $wishlist->getWishlistItems(true);
                        $arResult['CNT_ITEMS']=count($arResult['ITEMS']);
                    }else{
                        $arResult['RESULT']=false;
                        $arResult['ERROR']=$wishlist->getErrors();
                    }

                }
                break;
        }


        $json=json_encode($arResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->setFrameMode(false);
        $APPLICATION->RestartBuffer();
        while(ob_end_clean());
        header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
        echo $json;
        CMain::FinalActions();
        die();

    }
    die();
}

$arResult['ITEMS'] = $wishlist->getWishlistItems();
$arResult['CNT_ITEMS']=count($arResult['ITEMS']);

$this->IncludeComponentTemplate();
?>