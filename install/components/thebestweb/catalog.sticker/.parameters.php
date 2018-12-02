<?
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

global $APPLICATION;

$MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';

$arComponentParameters = array(
    "PARAMETERS" => array(

        "CACHE_TIME" => array(
            "DEFAULT" => 36000000,
        ),
        "CACHE_GROUPS" => array(
            "PARENT" => "CACHE_SETTINGS",
            "NAME" => Loc::getMessage($MODULE_LANG_PREFIX."_CACHE_GROUPS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        'SECTION_ID'=>array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => Loc::getMessage($MODULE_LANG_PREFIX."_SECTION_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => ''
        ),
        'IBLOCK_ID'=>array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => Loc::getMessage($MODULE_LANG_PREFIX."_IBLOCK_ID"),
            "TYPE" => "STRING",
            "DEFAULT" => '={$arParams["IBLOCK_ID"]}',
        ),
    ),
);
?>