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
            "NAME" => GetMessage("KOMBOX_CMP_FILTER_CACHE_GROUPS"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),

    ),
);
$arComponentParameters['PARAMETERS']['SECTION_ID'] = array(
    "PARENT" => "DATA_SOURCE",
    "NAME" => GetMessage("KOMBOX_CMP_FILTER_SECTION_ID"),
    "TYPE" => "STRING",
    "DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
);

$arComponentParameters['PARAMETERS']['SECTION_CODE'] = array(
    "PARENT" => "DATA_SOURCE",
    "NAME" => GetMessage("KOMBOX_CMP_FILTER_SECTION_CODE"),
    "TYPE" => "STRING",
    "DEFAULT" => '',
);
?>