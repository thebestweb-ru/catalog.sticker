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



$documentRoot = Main\Application::getDocumentRoot();

$jsParams=array();

if(!empty($arResult['ITEMS'])){
    foreach ($arResult['ITEMS'] as $KEY_ITEM=>$ITEM){
        $TYPE_OPTIONS=$ITEM['TYPE_OPTIONS'][$ITEM['TYPE']];

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

    $jsParams['TYPE']=$arResult['TYPE'];
    $jsParams['TYPE_OPTIONS']=$arResult['TYPE_OPTIONS'][$arResult['TYPE']];


}

debugmessage('Список групп для показа');
debugmessage($arResult);

?>
<script>
    jQuery( document ).ready(function($) {
       $.fn.TWB_CatalogSticker(<?=CUtil::PhpToJSObject($jsParams)?>);
    });
</script>
