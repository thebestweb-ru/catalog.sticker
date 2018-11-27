<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */
/** @global CUserTypeManager $USER_FIELD_MANAGER */


$arComponentDescription = array(
    "NAME" => GetMessage("OCEANDEVELOP_MAIN_COMPONENT_NAME"),
    "DESCRIPTION" => GetMessage("OCEANDEVELOP_MAIN_COMPONENT_NAME"),
    "ICON" => "/images/regions.gif",
    "SORT" => 500,
    "PATH" => array(
        "ID" => "oceandevelop_wishlist_components",
        "SORT" => 500,
        "NAME" => GetMessage("OCEANDEVELOP_LIST_COMPONENTS_FOLDER_NAME"),
    ),
);
?>