<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("OCEANDEVELOP_LIST_COMPONENT_NAME"),
	"DESCRIPTION" => GetMessage("OCEANDEVELOP_LIST_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/regions.gif",
	"SORT" => 500,
	"PATH" => array(
		"ID" => "oceandevelop_wishlist_components",
		"SORT" => 500,
		"NAME" => GetMessage("OCEANDEVELOP_LIST_COMPONENTS_FOLDER_NAME"),
	),
);

?>