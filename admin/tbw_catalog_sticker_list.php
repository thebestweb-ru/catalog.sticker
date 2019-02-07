<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    TBW\CatalogSticker\ListTable;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;

$MODULE_ID = 'thebestwebpro.catalog.sticker';
$MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';

if (!Loader::includeModule($MODULE_ID))
{
    $APPLICATION->ThrowException(Loc::getMessage($MODULE_LANG_PREFIX."_NOT_INSTALL"));
    return false;
}

if (!Loader::includeModule('iblock'))
{
    $APPLICATION->ThrowException(Loc::getMessage($MODULE_LANG_PREFIX."_IBLOCK_NOT_INSTALL"));
    return false;
}

$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_ID);
if($POST_RIGHT=="D")
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));


$sTableID=ListTable::getTableName();

$SITE_ID=$_REQUEST['site'] ? $_REQUEST['site'] : null;
if(!$SITE_ID){
    $APPLICATION->ThrowException(Loc::getMessage($MODULE_LANG_PREFIX."_NOT_FIND_SITE_ID"));
    return false;
}
$rsSites = CSite::GetList($by = "sort", $order = "desc", Array("ACTIVE" => "Y",'LID'=>$SITE_ID));
if (!$arSite = $rsSites->Fetch()) {
    $APPLICATION->ThrowException(Loc::getMessage($MODULE_LANG_PREFIX."_NOT_FIND_SITE_ID"));
    return false;
}

$rsSites = CSite::GetList($by="sort", $order="desc", Array("ACTIVE"=>"Y"));
while ($arSite = $rsSites->Fetch())
{
    $arSites[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
}

unset($rsSites);
unset($arSite);


if(count($arSites)<=1){
    $rsSites = CSite::GetList($by="sort", $order="desc", Array());
    while ($arSite = $rsSites->Fetch())
    {
        $arSites2[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
    }
}
unset($rsSites);
unset($arSite);


$by=$_REQUEST['by'] ? $_REQUEST['by'] : "ID";
$order=$_REQUEST['order'] ? $_REQUEST['order'] :  "desc";

$oSort = new CAdminSorting($sTableID, $by, $order);
$lAdmin = new CAdminList($sTableID, $oSort);

// ******************************************************************** //
//                           ФИЛЬТР                                     //
// ******************************************************************** //

// *********************** CheckFilter ******************************** //
// проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) global $$f;
    return count($lAdmin->arFilterErrors)==0;
}
// *********************** /CheckFilter ******************************* //

// опишем элементы фильтра
$FilterArr = Array(
    'find_site_id',
    "find_name",
    "find_date_start",
    "find_date_end",
    "find_active",
);


// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);
$arFilter=array();
// если все значения фильтра корректны, обработаем его
if (CheckFilter())
{
    $arFilter = Array();
    if(!empty($find_id))
        $arFilter["ID"]=$find_id;

    if(count($arSites)<=1){
        if(!empty($find_site_id))
            $arFilter["SITE_ID"]=$find_site_id;
    }else{
        $find_site_id=$SITE_ID;
        $arFilter["SITE_ID"]=$SITE_ID;
    }

    if(!empty($find_name))
        $arFilter["NAME"]=$find_name;

    if(!empty($find_date_start))
        $arFilter["DATE_START"]=$find_date_start;

    if(!empty($find_date_end))
        $arFilter["DATE_END"]=$find_date_end;

    if(!empty($find_active))
        $arFilter["ACTIVE"]=$find_active;

    if(empty($arFilter['ID'])) unset($arFilter['ID']);
    if(empty($arFilter['NAME'])) unset($arFilter['NAME']);
    if(empty($arFilter['ACTIVE'])) unset($arFilter['ACTIVE']);
    if(empty($arFilter['DATE_START'])) unset($arFilter['DATE_START']);
    if(empty($arFilter['DATE_END'])) unset($arFilter['DATE_END']);
}


// обработка одиночных и групповых действий
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
    // если выбрано "Для всех элементов"
    if($_REQUEST['action_target']=='selected')
    {
        $parametrs=array(
            'filter' => $arFilter,
            'order'=>array($by=>$order)
        );

        $rsData = ListTable::getList($parametrs);
        while($arRes = $rsData->Fetch())
            $arID[] = $arRes['ID'];
    }

    // пройдем по списку элементов
    foreach($arID as $ID)
    {
        if(strlen($ID)<=0)
            continue;
        $ID = IntVal($ID);

        // для каждого элемента совершим требуемое действие
        switch($_REQUEST['action'])
        {
            // удаление
            case "delete":
                @set_time_limit(0);
                $DB->StartTransaction();
                $result = ListTable::delete($ID);
                if (!$result->isSuccess())
                {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(Loc::getMessage($MODULE_LANG_PREFIX."_LIST_DEL_ERROR"), $ID);
                }
                $DB->Commit();
                break;
            case "activate":
            case "deactivate":
                $arFields["ACTIVE"] = ($_REQUEST['action']=="activate"? 'Y' : '');

                    $result = ListTable::update($ID, array(
                        'ACTIVE' => $arFields["ACTIVE"],
                    ));
                    if (!$result->isSuccess())
                        $lAdmin->AddGroupError(Loc::getMessage($MODULE_LANG_PREFIX."_LIST_SAVE_ERROR"), $ID);

                break;
        }
    }
}

// выберем список элементов
$parametrs=array(
    'filter' => $arFilter,
    'order'=>array($by=>$order)
);
$rsData = ListTable::getList($parametrs);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);
// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();
// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage($MODULE_LANG_PREFIX."_LIST_NAV")));

$lAdmin->AddHeaders(array(
    array(  "id"    =>"ID",
        "content"  =>"ID",
        "sort"    =>"ID",
        "align"    =>"right",
        "default"  =>true,
    ),
    array(  "id"    =>"SITE_ID",
        "content"  =>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_SITE_ID"),
        "sort"    =>"SITE_ID",
        "default"  =>true,
    ),
    array(  "id"    =>"NAME",
        "content"  =>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_NAME"),
        "sort"    =>"NAME",
        "default"  =>true,
    ),
    array(  "id"    =>"ACTIVE",
        "content"  =>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_ACTIVE"),
        "sort"    =>"ACTIVE",
        "default"  =>true,
    ),
    array(  "id"    =>"SORT",
        "content"  =>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_SORT"),
        "sort"    =>"SORT",
        "default"  =>true,
    ),
    array(  "id"    =>"TYPE",
        "content"  =>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_TYPE"),
        "sort"    =>"TYPE",
        "default"  =>true,
    ),
    array(  "id"    =>"DATE_START",
        "content"  =>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_DATE_START"),
        "sort"    =>"DATE_START",
        "default"  =>true,
    ),
    array(  "id"    =>"DATE_END",
        "content"  =>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_DATE_END"),
        "sort"    =>"DATE_END",
        "default"  =>true,
    ),

));

while($arRes = $rsData->NavNext(true, "f_"))
{
    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    $row->AddInputField("ID", array("size"=>40));
    $row->AddViewField("ID", '<a href="tbw_catalog_sticker_item_list.php?site='.$SITE_ID.'&LIST_ID='.$f_ID.'&lang='.LANG.'">'.$f_ID.'</a>');

    $row->AddInputField("SORT", array("size"=>20));

    $row->AddCheckField("ACTIVE");

    $arActions = Array();
    // редактирование элемента
    $arActions[] = array(
        "ICON"=>"view",
        "DEFAULT"=>true,
        "TEXT"=>Loc::getMessage($MODULE_LANG_PREFIX."_ITEM_LIST"),
        "ACTION"=>$lAdmin->ActionRedirect("tbw_catalog_sticker_item_list.php?site=".$SITE_ID."&LIST_ID=".$f_ID."&lang=".LANG)
    );
    // редактирование элемента
    $arActions[] = array(
        "ICON"=>"edit",
        "DEFAULT"=>false,
        "TEXT"=>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_ACTIONS_EDIT"),
        "ACTION"=>$lAdmin->ActionRedirect("tbw_catalog_sticker_list_item.php?site=".$SITE_ID."&ID=".$f_ID."&lang=".LANG)
    );


    // удаление элемента
    if ($POST_RIGHT>="W")
        $arActions[] = array(
            "ICON"=>"delete",
            "TEXT"=>Loc::getMessage($MODULE_LANG_PREFIX."_LIST_ACTIONS_DEL"),
            "ACTION"=>"if(confirm('".Loc::getMessage($MODULE_LANG_PREFIX.'_LIST_ACTIONS_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete",'site='.$SITE_ID)
        );
    if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
        unset($arActions[count($arActions)-1]);

    // применим контекстное меню к строке
    $row->AddActions($arActions);
}
// резюме таблицы
$lAdmin->AddFooter(
    array(
        array("title"=>Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
        array("counter"=>true, "title"=>Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
    )
);

// групповые действия
$lAdmin->AddGroupActionTable(Array(
    "delete"=>Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
    "activate" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"),
    "deactivate" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
));
$aContext = array(
    array(
        "TEXT"=>Loc::getMessage($MODULE_LANG_PREFIX."_ADD"),
        "LINK"=>"tbw_catalog_sticker_list_item.php?site=".$SITE_ID."&lang=".LANG,
        "TITLE"=>Loc::getMessage($MODULE_LANG_PREFIX."_ADD"),
        "ICON"=>"btn_new",
    ),
);
// ******************************************************************** //
//                ВЫВОД                                                 //
// ******************************************************************** //

$lAdmin->AddAdminContextMenu($aContext);
// альтернативный вывод
$lAdmin->CheckListMode();

// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage($MODULE_LANG_PREFIX."_LIST_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// ******************************************************************** //
//                ВЫВОД ФИЛЬТРА                                         //
// ******************************************************************** //

// создадим объект фильтра
$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(
        "ID",
        Loc::getMessage($MODULE_LANG_PREFIX."_LIST_SITE_ID"),
        Loc::getMessage($MODULE_LANG_PREFIX."_LIST_NAME"),
        Loc::getMessage($MODULE_LANG_PREFIX."_LIST_DATE_START"),
        Loc::getMessage($MODULE_LANG_PREFIX."_LIST_DATE_END"),
        Loc::getMessage($MODULE_LANG_PREFIX."_LIST_ACTIVE"),
    )
);

?>
    <form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
        <?echo bitrix_sessid_post();?>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <input type="hidden" name="site" value="<?=$SITE_ID?>">
        <?$oFilter->Begin();?>
        <tr>
            <td><?="ID"?>:</td>
            <td>
                <input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>">
            </td>
        </tr>
        <?if(count($arSites)<=1):?>
            <tr>
                <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_LIST_SITE_ID")?>:</td>
                <td>
                    <? echo SelectBoxFromArray("find_site_id", array("REFERENCE" => array_keys ($arSites2), "REFERENCE_ID" => array_keys ($arSites2)), htmlspecialchars($find_site_id), Loc::getMessage("POST_ALL"), "".(count($arSites)<=1 ? '' : 'disabled'));?>
                </td>
            </tr>
        <?else:?>
            <tr>
                <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_LIST_SITE_ID")?>:</td>
                <td>
                    <? echo SelectBoxFromArray("find_site_id", array("REFERENCE" => array_keys ($arSites), "REFERENCE_ID" => array_keys ($arSites)), htmlspecialchars($find_site_id), Loc::getMessage("POST_ALL"), "".(count($arSites)<=1 ? '' : 'disabled'));?>
                </td>
            </tr>
        <?endif;?>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_LIST_NAME")?>:</td>
            <td>
                <input type="text" name="find_name" size="47" value="<?echo htmlspecialchars($find_name)?>">
            </td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_LIST_DATE_START")?>:</td>
            <td>
                <input type="text" name="find_date_start" size="47" value="<?echo htmlspecialchars($find_date_start)?>">
            </td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_LIST_DATE_END")?>:</td>
            <td>
                <input type="text" name="find_date_end" size="47" value="<?echo htmlspecialchars($find_date_end)?>">
            </td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_LIST_ACTIVE")?>:</td>
            <td>
                <?
                $arr = array(
                    "reference" => array(
                        Loc::getMessage("MAIN_YES"),
                        Loc::getMessage("MAIN_NO"),
                    ),
                    "reference_id" => array(
                        true,
                        false,
                    )
                );
                echo SelectBoxFromArray("find_active", $arr, $find_active, Loc::getMessage("MAIN_ALL"), "");
                ?>
            </td>
        </tr>
        <?
        $oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
        $oFilter->End();
        ?>
    </form>

<?
// выведем таблицу списка элементов
$lAdmin->DisplayList();
?>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>