<?
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';

$arComponentDescription = array(
	"NAME" => Loc::getMessage($MODULE_LANG_PREFIX."_COMPONENT_NAME"),
	"DESCRIPTION" => Loc::getMessage($MODULE_LANG_PREFIX."_COMPONENT_DESCRIPTION"),
	"ICON" => "/images/regions.gif",
	"SORT" => 500,
	"PATH" => array(
		"ID" => "thebestweb",
		"SORT" => 500,
		"NAME" => Loc::getMessage($MODULE_LANG_PREFIX."_COMPONENTS_FOLDER_NAME"),
	),
);

?>