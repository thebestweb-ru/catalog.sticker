<?

use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

$module_id = 'thebestweb.catalog.sticker';

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::registerAutoLoadClasses(
    $module_id,
    array(
        "\TheBestWeb\CatalogSticker\ListTable"=> "/lib/tbw_catalog_sticker_table.php",
        "\TheBestWeb\CatalogSticker\ListSectionsTable"=> "/lib/tbw_catalog_sticker_table.php",
        "\TheBestWeb\CatalogSticker\ItemTable"=> "/lib/tbw_catalog_sticker_table.php",
        "\TheBestWeb\CatalogSticker"=> "/lib/tbw_catalog_sticker.php",
    )
);

?>