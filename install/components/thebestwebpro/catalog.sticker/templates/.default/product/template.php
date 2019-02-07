<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $ITEM
 * @var array $TYPE_OPTIONS
 * @var CatalogSectionComponent $component
 */
//debugmessage($TYPE_OPTIONS);
$arFilter = Array(
    "ID"=>IntVal($TYPE_OPTIONS['ID']),
    "IBLOCK_ID"=>IntVal($TYPE_OPTIONS['IBLOCK_ID']),
    "ACTIVE"=>"Y",
);
$res = CIBlockElement::GetList(Array(), $arFilter, Array("NAME","PREVIEW_PICTURE"));
if($ar_fields = $res->GetNext())
{
    $ar_fields['PREVIEW_PICTURE']=CFile::GetFileArray($ar_fields['PREVIEW_PICTURE']);
    //debugmessage($ar_fields);
}
?>
<div class="sticker-product">
    <img src="<?=$ar_fields['PREVIEW_PICTURE']['SRC']?>">
</div>