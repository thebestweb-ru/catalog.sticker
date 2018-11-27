<?use \Bitrix\Main\Localization\Loc;
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

$SITE_ID=$_REQUEST['site'] ? $_REQUEST['site'] : null;
if(!$SITE_ID){
    $APPLICATION->ThrowException(GetMessage("MODULE_WISHLIST_NOT_FIND_SITE_ID"));
    return false;
}
$rsSites = CSite::GetList($by = "sort", $order = "desc", Array("ACTIVE" => "Y",'LID'=>$SITE_ID));
if (!$arSite = $rsSites->Fetch()) {
    $APPLICATION->ThrowException(GetMessage("MODULE_WISHLIST_NOT_FIND_SITE_ID"));
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

    // В данном случае проверять нечего.
    // В общем случае нужно проверять значения переменных $find_имя
    // и в случае возниконовения ошибки передавать ее обработчику
    // посредством $lAdmin->AddFilterError('текст_ошибки').

    return count($lAdmin->arFilterErrors)==0; // если ошибки есть, вернем false;
}
// *********************** /CheckFilter ******************************* //

// опишем элементы фильтра
$FilterArr = Array(
    "find_id",
    'find_site_id',
    "find_user_id",
    "find_user_email",
    "find_wishlist_code",
    "find_send_to_crm",
    "find_send_to_email",
);


// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значения фильтра корректны, обработаем его
if (CheckFilter())
{
    $arFilter = Array();

    if(count($arSites)<=1){
        if(!empty($find_site_id))
            $arFilter["SITE_ID"]=$find_site_id;
    }else{
        $find_site_id=$SITE_ID;
        $arFilter["SITE_ID"]=$SITE_ID;
    }

    if(!empty($find_id))
        $arFilter["ID"]=$find_id;

    if(!empty($find_user_id))
        $arFilter["USER_ID"]=$find_user_id;

    if(!empty($find_user_email))
        $arFilter["USER_EMAIL"]=$find_user_email;

    if(!empty($find_wishlist_code))
        $arFilter["WISHLIST_CODE"]=$find_wishlist_code;

    if(!empty($find_send_to_crm))
        $arFilter["SEND_TO_CRM"]=$find_send_to_crm;

    if(!empty($find_send_to_email))
        $arFilter["SEND_TO_EMAIL"]=$find_send_to_email;


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

        $rsData = WishlistProListTable::getList($parametrs);
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
                $result = WishlistProListTable::delete($ID);
                if (!$result->isSuccess())
                {
                    $DB->Rollback();
                    $lAdmin->AddGroupError(GetMessage("MODULE_WISHLIST_LIST_DEL_ERROR"), $ID);
                }
                $DB->Commit();
                break;
        }
    }
}

// выберем список элементов
$parametrs=array(
    'filter' => $arFilter,
    'order'=>array($by=>$order)
);
$rsData = WishlistProListTable::getList($parametrs);

// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);



// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();

// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(GetMessage("MODULE_WISHLIST_LIST_NAV")));

$lAdmin->AddHeaders(array(
    array(  "id"    =>"ID",
        "content"  =>"ID",
        "sort"    =>"ID",
        "align"    =>"right",
        "default"  =>true,
    ),
    array(  "id"    =>"SITE_ID",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_SITE_ID"),
        "sort"    =>"SITE_ID",
        "default"  =>true,
    ),
    array(  "id"    =>"DATE_CREATE",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_DATE_CREATE"),
        "sort"    =>"DATE_CREATE",
        "default"  =>true,
    ),
    array(  "id"    =>"DATE_CHANGE",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_DATE_CHANGE"),
        "sort"    =>"DATE_CHANGE",
        "default"  =>true,
    ),
    array(  "id"    =>"USER_ID",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_USER_ID"),
        "sort"    =>"USER_ID",
        "default"  =>true,
    ),
    array(  "id"    =>"USER_EMAIL",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_USER_EMAIL"),
        "default"  =>true,
    ),
    array(  "id"    =>"SEND_TO_CRM",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_SEND_TO_CRM"),
        "sort"    =>"SEND_TO_CRM",
        "default"  =>true,
    ),
    array(  "id"    =>"SEND_TO_EMAIL",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_SEND_TO_EMAIL"),
        "sort"    =>"SEND_TO_CRM",
        "default"  =>true,
    ),
    array(  "id"    =>"WISHLIST_CODE",
        "content"  =>GetMessage("MODULE_WISHLIST_LIST_WISHLIST_CODE"),
        "default"  =>true,
    ),

));

while($arRes = $rsData->NavNext(true, "f_"))
{
    // создаем строку. результат - экземпляр класса CAdminListRow
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    $row->AddInputField("ID", array("size"=>40));
    $row->AddViewField("ID", '<a href="oceandevelop_wishlist_list_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_ID.'</a>');
    if($f_USER_ID){
        $row->AddViewField("USER_ID", '<a href="user_edit.php?ID='.$f_USER_ID.'&lang='.LANG.'">'.$f_USER_ID.'</a>');
    }else{
        $row->AddViewField("USER_ID", 'Анонимный');
    }

    if($f_SEND_TO_CRM){
        $row->AddViewField("SEND_TO_CRM", 'Да');
    }else{
        $row->AddViewField("SEND_TO_CRM", 'Нет');
    }

    if($f_SEND_TO_EMAIL){
        $row->AddViewField("SEND_TO_EMAIL", 'Да');
    }else{
        $row->AddViewField("SEND_TO_EMAIL", 'Нет');
    }


    // сформируем контекстное меню
    $arActions = Array();

    // редактирование элемента
    $arActions[] = array(
        "ICON"=>"edit",
        "DEFAULT"=>true,
        "TEXT"=>GetMessage("MODULE_WISHLIST_LIST_ACTIONS_EDIT"),
        "ACTION"=>$lAdmin->ActionRedirect("oceandevelop_wishlist_list_edit.php?ID=".$f_ID."&lang=".LANG)
    );

    // удаление элемента
    if ($POST_RIGHT>="W")
        $arActions[] = array(
            "ICON"=>"delete",
            "TEXT"=>GetMessage("MODULE_WISHLIST_LIST_ACTIONS_DEL"),
            "ACTION"=>"if(confirm('".GetMessage('MODULE_WISHLIST_LIST_ACTIONS_DEL_CONF')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete",'site='.$SITE_ID)
        );
    if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
        unset($arActions[count($arActions)-1]);

    // применим контекстное меню к строке
    $row->AddActions($arActions);
}
// резюме таблицы
$lAdmin->AddFooter(
    array(
        array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
        array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
    )
);

// групповые действия
$lAdmin->AddGroupActionTable(Array(
    "delete"=>GetMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
));
// ******************************************************************** //
//                ВЫВОД                                                 //
// ******************************************************************** //

// альтернативный вывод
$lAdmin->CheckListMode();

// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage("TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// ******************************************************************** //
//                ВЫВОД ФИЛЬТРА                                         //
// ******************************************************************** //

// создадим объект фильтра
$oFilter = new CAdminFilter(
    $sTableID."_filter",
    array(
        "ID",
        GetMessage("MODULE_WISHLIST_LIST_SITE_ID"),
        GetMessage("MODULE_WISHLIST_LIST_USER_ID"),
        GetMessage("MODULE_WISHLIST_LIST_USER_EMAIL"),
        GetMessage("MODULE_WISHLIST_LIST_WISHLIST_CODE"),
        GetMessage("MODULE_WISHLIST_LIST_SEND_TO_CRM"),
        GetMessage("MODULE_WISHLIST_LIST_SEND_TO_EMAIL"),
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
                <td><?=GetMessage("MODULE_WISHLIST_LIST_SITE_ID")?>:</td>
                <td>
                    <? echo SelectBoxFromArray("find_site_id", array("REFERENCE" => array_keys ($arSites2), "REFERENCE_ID" => array_keys ($arSites2)), htmlspecialchars($find_site_id), GetMessage("POST_ALL"), "".(count($arSites)<=1 ? '' : 'disabled'));?>
                </td>
            </tr>
        <?else:?>
            <tr>
                <td><?=GetMessage("MODULE_WISHLIST_LIST_SITE_ID")?>:</td>
                <td>
                    <? echo SelectBoxFromArray("find_site_id", array("REFERENCE" => array_keys ($arSites), "REFERENCE_ID" => array_keys ($arSites)), htmlspecialchars($find_site_id), GetMessage("POST_ALL"), "".(count($arSites)<=1 ? '' : 'disabled'));?>
                </td>
            </tr>
        <?endif;?>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_USER_ID")?>:</td>
            <td>
                <input type="text" name="find_user_id" size="47" value="<?echo htmlspecialchars($find_user_id)?>">
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_USER_EMAIL")?>:</td>
            <td>
                <input type="text" name="find_user_email" size="47" value="<?echo htmlspecialchars($find_user_email)?>">
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_WISHLIST_CODE")?>:</td>
            <td>
                <input type="text" name="find_wishlist_code" size="47" value="<?echo htmlspecialchars($find_wishlist_code)?>">
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_SEND_TO_CRM")?>:</td>
            <td>
                <?
                $arr = array(
                    "reference" => array(
                        GetMessage("MODULE_WISHLIST_LIST_POST_YES"),
                        GetMessage("MODULE_WISHLIST_LIST_POST_NO"),
                    ),
                    "reference_id" => array(
                        true,
                        false,
                    )
                );
                echo SelectBoxFromArray("find_send_to_crm", $arr, $find_send_to_crm, GetMessage("MODULE_WISHLIST_LIST_POST_ALL"), "");
                ?>
            </td>
        </tr>
        <tr>
            <td><?=GetMessage("MODULE_WISHLIST_LIST_SEND_TO_EMAIL")?>:</td>
            <td>
                <?
                $arr = array(
                    "reference" => array(
                        GetMessage("MODULE_WISHLIST_LIST_POST_YES"),
                        GetMessage("MODULE_WISHLIST_LIST_POST_NO"),
                    ),
                    "reference_id" => array(
                        true,
                        false,
                    )
                );
                echo SelectBoxFromArray("find_send_to_email", $arr, $find_send_to_email, GetMessage("MODULE_WISHLIST_LIST_POST_ALL"), "");
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