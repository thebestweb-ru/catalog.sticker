<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CatalogProductsViewedComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 * @var string $templateFolder
 */

if(method_exists($this, 'setFrameMode'))
    $this->setFrameMode(true);

if($arParams['INCLUDE_JQUERY']=='Y')
    CJSCore::Init(array("Jquery2"));

$documentRoot = Main\Application::getDocumentRoot();

$jsParams=array();

if(!empty($arResult['ITEMS'])){
    ?>
    <div id="sticker-items" style="display:none">
    <?
    foreach ($arResult['ITEMS'] as $KEY_ITEM=>$ITEM){
        $TYPE_OPTIONS=$ITEM['TYPE_OPTIONS'][$ITEM['TYPE']];
        if(empty($TYPE_OPTIONS))
            continue;

        $item_id="sticker-".$arResult['ID']."-".$ITEM['ID'];
        $jsParams['ITEMS_ID'][]=$item_id;

        $templatePath = strtolower($ITEM['TYPE']).'/template.php';
        $file = new Main\IO\File($documentRoot.$templateFolder.'/'.$templatePath);
        if ($file->isExists())
        {
            ?>
            <div id="<?=$item_id?>" data-entity="sticker">
            <?
            include($file->getPath());
            ?>
            </div>
            <?
        }
    }
    ?>
    </div>
        <?
    $jsParams['TYPE']=$arResult['TYPE'];
    $jsParams['TYPE_OPTIONS']=$arResult['TYPE_OPTIONS'][$arResult['TYPE']];
    $jsParams['PRODUCT_ITEM_SELECTOR']='[data-entity="item"]';
    $jsParams['PRODUCT_ROW_SELECTOR']='[data-entity="items-row"]';
    $jsParams['PRODUCT_CONTAINER_SELECTOR']='[data-entity]';
    $jsParams['STICKER_SELECTOR']='[data-entity="sticker"]';
        ?>
    <script>
        jQuery( document ).ready(function($) {
            $('#sticker-items').TWB_CatalogSticker(<?=CUtil::PhpToJSObject($jsParams)?>);
        });
    </script>
<?
}