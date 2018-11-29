<?
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
$iModuleID = "thebestweb.catalog.sticker";
$iModuleLangID = "TBW_CATALOG_STICKER";

if ($APPLICATION->GetGroupRight($iModuleID) != "D") {
    $rsSites = CSite::GetList($by = "sort", $order = "desc", Array("ACTIVE" => "Y"));
    while ($arSite = $rsSites->Fetch()) {
        $Sites[] = $arSite;
    }

    if (count($Sites) == 1)//If one site
    {
        $Lists = array(
            "icon" => "default_menu_icon",
            "page_icon" => "default_page_icon",
            "text" => Loc::getMessage($iModuleLangID."_ADMIN_MENU_LIST_TEXT"),
            "title" => Loc::getMessage($iModuleLangID."_ADMIN_MENU_LIST_TITLE"),
            "url" => "tbw_catalog_sticker_list.php?lang=" . LANGUAGE_ID . "&site=" . $Sites[0]['LID'] . "",
        );
    } else//If some site
    {

        foreach ($Sites as $Site) {
            $Lists_items[] = array(
                "text" => '[' . $Site['LID'] . '] ' . $Site['NAME'],
                "url" => "tbw_catalog_sticker_list.php?lang=" . LANGUAGE_ID . '&site=' . $Site['LID'],
                "title" => $Site['NAME']
            );
        }
        $Lists = array(
            "icon" => "default_menu_icon",
            "page_icon" => "default_page_icon",
            "text" => Loc::getMessage($iModuleLangID."_ADMIN_MENU_LIST_TEXT"),
            "title" => Loc::getMessage($iModuleLangID."_ADMIN_MENU_LIST_TITLE"),
            "items" => $Lists_items,
        );

    }
    $aMenu = array(
        "parent_menu" => "global_menu_services",
        "icon" => "fileman_sticker_icon",
        "page_icon" => "default_page_icon",
        "text" => Loc::getMessage($iModuleLangID."_ADMIN_MENU_TEXT"),
        "title" => Loc::getMessage($iModuleLangID."_ADMIN_MENU_TEXT"),
        "items" => array($Lists),
        "more_url"=> array("tbw_catalog_sticker_list_item.php","tbw_catalog_sticker_item.php","tbw_catalog_sticker_item_list.php"),
    );
    return $aMenu;
}
?>