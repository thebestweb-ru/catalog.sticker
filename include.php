<?

use Bitrix\Main\Application;

$module_id = 'thebestweb.catalog.sticker';


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