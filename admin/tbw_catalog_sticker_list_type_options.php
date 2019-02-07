<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    TBW\CatalogSticker\ListTable;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

$MODULE_ID = 'thebestwebpro.catalog.sticker';
$MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';

if (!Loader::includeModule($MODULE_ID))
{
    $APPLICATION->ThrowException(Loc::getMessage($MODULE_LANG_PREFIX."_NOT_INSTALL"));
    return false;
}



if(!empty($_REQUEST["ID"]))
    $ID=intval($_REQUEST["ID"]);

if($ID>0){
    $result = ListTable::GetById($ID);
    if($Item=$result->fetch()){

    }
}

if($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["TYPE"])) {
    require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_js.php");

    switch ($_REQUEST["TYPE"]){
        case 'POSITIONS':
            ?>
            <tr>
                <td class="adm-detail-content-cell-l"><span class="required">*</span><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_POSITIONS")?></td>
                <td class="adm-detail-content-cell-r">
                    <table id="options_position">
                        <thead></thead>
                        <tbody>
                        <?if(!empty($Item['TYPE_OPTIONS']['POSITIONS'])):?>
                            <?foreach($Item['TYPE_OPTIONS']['POSITIONS'] as $key=>$item):?>
                                <tr>
                                    <td><input type="text" name="TYPE_OPTIONS[POSITIONS][<?=$key?>]" value="<?=$item?>" size="10"></td>
                                </tr>
                            <?endforeach;?>
                        <?endif;?>
                        <tr id="clone_row">
                            <td><input type="text" name="TYPE_OPTIONS[POSITIONS][]" size="10"></td>
                        </tr>
                        </tbody>
                    </table>
                    <input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onclick="cloneRow('options_position','clone_row')">
                </td>
            </tr>

            <script>
                function cloneRow(table_id,row_id) {
                    var row = document.getElementById(row_id); // find row to copy
                    var table = BX(table_id); // find table to append to
                    var clone = row.cloneNode(true); // copy children too
                    clone.id = "newID"; // change id or other attributes/contents
                    table.appendChild(clone); // add new row to end of table
                }
            </script>
            <?
            break;
        case 'FIXED':
            ?>
            <tr>
                <td class="adm-detail-content-cell-l"><span class="required">*</span><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_FIXED")?></td>
                <td class="adm-detail-content-cell-r"><input type="text" name="TYPE_OPTIONS[FIXED]" value="<?=$Item['TYPE_OPTIONS']['FIXED'];?>" size="10" ></td>
            </tr>
            <?
            break;
        case 'FIXED_POSITIONS':
            ?>
            <tr>
                <td class="adm-detail-content-cell-l"><span class="required">*</span><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_FIXED_POSITIONS")?></td>
                <td class="adm-detail-content-cell-r"><input type="text" name="TYPE_OPTIONS[FIXED_POSITIONS]" value="<?=$Item['TYPE_OPTIONS']['FIXED_POSITIONS'];?>" size="10" ></td>
            </tr>
            <?
            break;

    }
    require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_js.php");
}
exit;
?>