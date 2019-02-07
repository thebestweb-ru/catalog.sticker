<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var string $componentPath
 * @var string $componentName
 * @var array $arCurrentValues
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

global $APPLICATION;

$MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';

CBitrixComponent::includeComponentClass($componentName);

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

        "INCLUDE_JQUERY" => array(
            "NAME" => Loc::getMessage($MODULE_LANG_PREFIX."_INCLUDE_JQUERY"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "N",
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