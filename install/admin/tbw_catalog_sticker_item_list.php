<?
$path='/thebestwebpro.catalog.sticker/admin/tbw_catalog_sticker_item_list.php';
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules".$path)){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules".$path);
}elseif(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/modules".$path)){
    require($_SERVER["DOCUMENT_ROOT"]."/local/modules".$path);
}