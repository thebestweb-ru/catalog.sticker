<?
namespace TBW;

use Bitrix\Main\Application,
    Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc;

$module_id = 'thebestwebpro.catalog.sticker';


Loc::loadMessages(__FILE__);

Loader::registerAutoLoadClasses(
    $module_id,
    array(
        "\TBW\CatalogSticker\ListTable"=> "/lib/tbw_catalog_sticker_table.php",
        "\TBW\CatalogSticker\ListSectionsTable"=> "/lib/tbw_catalog_sticker_table.php",
        "\TBW\CatalogSticker\ItemTable"=> "/lib/tbw_catalog_sticker_table.php",
    )
);
class CCatalogSticker {

    const MODULE_ID = 'thebestwebpro.catalog.sticker';
    const MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';

    public function __construct()
    {

    }

    public static function GetTypeGroupStickers(){
        return array(
            'POSITIONS'=>Loc::getMessage(self::MODULE_LANG_PREFIX."_POSITIONS"),
            'FIXED'=>Loc::getMessage(self::MODULE_LANG_PREFIX."_FIXED"),
            'FIXED_POSITIONS'=>Loc::getMessage(self::MODULE_LANG_PREFIX."_FIXED_POSITIONS"),
        );
    }

    public static function GetTypeStickers(){
        return array(
            'HTML'=>Loc::getMessage(self::MODULE_LANG_PREFIX."_HTML"),
            'PICTURE'=>Loc::getMessage(self::MODULE_LANG_PREFIX."_PICTURE"),
            'VIDEO'=>Loc::getMessage(self::MODULE_LANG_PREFIX."_VIDEO"),
            'PRODUCT'=>Loc::getMessage(self::MODULE_LANG_PREFIX."_PRODUCT"),
        );
    }
}
?>