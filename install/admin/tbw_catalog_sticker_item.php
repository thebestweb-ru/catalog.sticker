<?
use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use \Oceandevelop\Wishlist\WishlistProListTable;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;

$module_id = 'oceandevelop.wishlist';


if (!Loader::includeModule($module_id))
{
    $APPLICATION->ThrowException(GetMessage("MODULE_WISHLIST_NOT_INSTALL"));
    return false;
}
if (!Loader::includeModule('iblock'))
{
    $APPLICATION->ThrowException(GetMessage("MODULE_WISHLIST_IBLOCK_NOT_INSTALL"));
    return false;
}

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($POST_RIGHT=="D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID=WishlistProListTable::getTableName();


$aTabs[]=array("DIV" => "edit1", "TAB" => GetMessage("MODULE_WISHLIST_TAB"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("MODULE_WISHLIST_TAB"));
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);

// выборка данных
if($ID>0)
{
    $result = WishlistProListTable::GetByID($ID);
    if($Item=$result->fetch()){
        $ID=$Item['ID'];
        $bVarsFromForm = true;
        $WISHLIST=new ODWishlist($Item['WISHLIST_CODE']);
        $ITEMS=$WISHLIST->getWishlistItems(true);
        $WISHLIST=$WISHLIST->getWishlist();
    }
}

$products_column=array(
        'ID'=>'ID',
        'NAME'=>'Название',
        'ACTIVE'=>'Активность',
        'PREVIEW_PICTURE'=>'Изображение',
        'CML2_ARTICLE'=>'Артикул',
        'AVAILABLE'=>'Доступен к покупке',
);
foreach($ITEMS as $ITEM){
    $products_column_variable[]=array(
        'ID'=>$ITEM['ID'],
        'NAME'=>'<a href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$ITEM['IBLOCK_ID'].'&type=catalog&ID='.$ITEM['ID'].'&lang='.LANG.'">'.$ITEM['NAME'].'</a>',
        'ACTIVE'=>$ITEM['ACTIVE'],
        'PREVIEW_PICTURE'=>'<img src="'.$ITEM['PREVIEW_PICTURE']['SRC'].'" width="200">',
        'CML2_ARTICLE'=>$ITEM['CML2_ARTICLE'],
        'AVAILABLE'=>$ITEM['CATALOG']['AVAILABLE'],
    );
}

$APPLICATION->SetTitle(($ID>0? GetMessage("MODULE_WISHLIST_TITLE").'['.$ID.'] '.$Item['NAME'] : GetMessage("MODULE_WISHLIST_TITLE")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($message)
    echo $message->Show();

?>
    <form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
        <?echo bitrix_sessid_post();?>
        <?
        // отобразим заголовки закладок
        $tabControl->Begin();
        ?>
        <?
        //Вкладка ОСНОВНЫЕ
        $tabControl->BeginNextTab();
        ?>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_SITE_ID")?></td>
            <td><label><?=$WISHLIST['SITE_ID'];?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_DATE_CREATE")?></td>
            <td><label><?=$WISHLIST['DATE_CREATE']->toString();?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_DATE_CHANGE")?></td>
            <td><label><?=$WISHLIST['DATE_CHANGE']->toString();?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_USER_ID")?></td>
            <td><label>
                    <?if($WISHLIST['USER_ID']):?>
                    <a href="user_edit.php?ID=<?=$WISHLIST['USER_ID'];?>&lang=<?=LANG?>"><?=$WISHLIST['USER_ID'];?></a>
                    <?else:?>
                    <?=GetMessage("MODULE_WISHLIST_LIST_ANONIM")?>
                    <?endif;?>
                </label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_USER_NAME")?></td>
            <td><label><?=$WISHLIST['USER_NAME'];?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_USER_EMAIL")?></td>
            <td><label><?=$WISHLIST['USER_EMAIL'];?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_USER_PHONE")?></td>
            <td><label><?=$WISHLIST['USER_PHONE'];?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_WISHLIST_CODE")?></td>
            <td><label><?=$WISHLIST['WISHLIST_CODE'];?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_SEND_TO_CRM")?></td>
            <td><label><?=$WISHLIST['SEND_TO_CRM'] ? GetMessage("MODULE_WISHLIST_LIST_YES") :GetMessage("MODULE_WISHLIST_LIST_NO");?></label></td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_SEND_TO_EMAIL")?></td>
            <td><label><?=$WISHLIST['SEND_TO_EMAIL'] ? GetMessage("MODULE_WISHLIST_LIST_YES") :GetMessage("MODULE_WISHLIST_LIST_NO");?></label></td>
        </tr>
        <?
        // завершаем интерфейс закладки
        $tabControl->End();
        ?>

            <table class="adm-s-order-table-ddi-table" style="width: 100%; text-align: center;" id="sale_order_view_product_table">
                <thead >
                <tr>
                    <?foreach($products_column as $colId => $name):?>
                       <td><?=htmlspecialcharsbx($name)?></td>
                    <?endforeach;?>
                </tr>
                </thead>
                <tbody style="border: 1px solid rgb(221, 221, 221);" id="sale-adm-order-basket-loading-row">
                <?foreach($products_column_variable as $key_item=>$item):?>
                <tr>
                    <?$i=0;
                    foreach($item as $key=>$value):?>
                        <td>
                           <?=$value?>
                        </td>
                    <?endforeach;?>
                </tr>
                <?endforeach;?>
                </tbody>

            </table>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <?if($ID>0 && !$bCopy):?>
            <input type="hidden" name="ID" value="<?=$ID?>">
        <?endif;?>
        <?
        $tabControl->ShowWarnings("post_form", $message);
        ?>
    </form>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>