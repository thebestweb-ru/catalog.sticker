<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    TheBestWeb\CatalogSticker\ListTable,
    TheBestWeb\CatalogSticker\ListSectionsTable;

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);

global $USER, $APPLICATION, $DB;

$MODULE_ID = 'thebestweb.catalog.sticker';
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



$rsSites = CSite::GetList($by="sort", $order="desc", Array());
while ($arSite = $rsSites->Fetch())
{
    $arSites[$arSite['LID']] = '['.$arSite['LID'].'] '.$arSite['NAME'];
}

unset($rsSites);
unset($arSite);

$sTableID=ListTable::getTableName();

$aTabs[]=array("DIV" => "edit1", "TAB" => Loc::getMessage($MODULE_LANG_PREFIX."_TAB"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage($MODULE_LANG_PREFIX."_TAB"));
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);
$message = null;		// сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

if(
    $REQUEST_METHOD == "POST" // проверка метода вызова страницы
    &&
    ($save!="" || $apply!="") // проверка нажатия кнопок "Сохранить" и "Применить"
    &&
    $POST_RIGHT=="W"          // проверка наличия прав на запись для модуля
    &&
    check_bitrix_sessid()     // проверка идентификатора сессии
)
{

    if($SETTINGS['CRM']=='Y'){
        if(empty($SETTINGS['CRM_URL']) || empty($SETTINGS['CRM_LOGIN']) || empty($SETTINGS['CRM_PASSWORD']))
            $message = new CAdminMessage(array(
                'MESSAGE' =>  GetMessage("MODULE_WISHLIST_CRM_NOT_REQUIRED_FIELDS"),
                'TYPE' => 'ERROR',
                'DETAILS' => '',
                'HTML' => true
            ));
    }

    if(empty($SETTINGS['EVENT_ID']))
        $message = new CAdminMessage(array(
            'MESSAGE' =>  GetMessage("MODULE_WISHLIST_SETTINGS_NOT_EMAIL_EVENT"),
            'TYPE' => 'ERROR',
            'DETAILS' => '',
            'HTML' => true
        ));

    $SETTINGS['EVENT_NAME']='MODULE_WISHLIST_SEND_LIST';
    // обработка данных формы
    $arFields = Array(
        "ID"                => $ID,
        "SITE_ID"           => $SITE_ID,
        "SETTINGS"           => $SETTINGS,
    );

    if(!$message){
        // сохранение данных
        if($action =='add')
        {
            $result = WishlistProOptionsTable::add($arFields);
            if (!$result->isSuccess())
            {
                $message = $result->getErrorMessages();
            }

        }
        else
        {
            $result = WishlistProOptionsTable::update($ID,$arFields);
            if (!$result->isSuccess())
            {
                $message = $result->getErrorMessages();
            }
        }
    }

    if($result)
    {
        // если сохранение прошло удачно - перенаправим на новую страницу
        // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
        if ($apply != "")
            // если была нажата кнопка "Применить" - отправляем обратно на форму.
            LocalRedirect("/bitrix/admin/oceandevelop_wishlist_options.php?lang=".LANG."&site=".$SITE_ID."&mess=ok&".$tabControl->ActiveTabParam());
        else
            // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
            LocalRedirect("/bitrix/admin/oceandevelop_wishlist_options.php?lang=".LANG."&site=".$SITE_ID."&mess=ok&".$tabControl->ActiveTabParam());
    }
    else
    {
        // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if($e = $APPLICATION->GetException())
            $message = new CAdminMessage(GetMessage("MODULE_WISHLIST_SETTINGS_SAVE_ERROR"), $e);
        $bVarsFromForm = true;
    }
}
// выборка данных
if($ID>0 )
{
    $result = ListTable::GetById($ID);
    if($Item=$result->fetch()){
        $ID=$Item['ID'];
        $bVarsFromForm = true;
    }
}

$APPLICATION->SetTitle(($ID>0? Loc::getMessage($MODULE_LANG_PREFIX."_TITLE_EDIT").'['.$ID.'] '.$Item['NAME'] : Loc::getMessage($MODULE_LANG_PREFIX."_TITLE_ADD")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($_REQUEST["mess"] == "ok" && !empty($SITE_ID))
    CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("MODULE_WISHLIST_SETTINGS_SAVE_OK"), "TYPE"=>"OK"));

if($message)
    echo $message->Show();

?>
    <form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
        <?echo bitrix_sessid_post();?>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <input type="hidden" name="action" value="<?=$bVarsFromForm ? 'update' : 'add';?>">
        <input type="hidden" name="ID" value="<?=$ID ? $ID : '';?>">

        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <?if(!empty($arSites)):?>
            <?foreach ($arSites as $lid=>$site_name):?>

            <?endforeach;?>
        <?endif;?>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_NAME")?></td>
            <td><input type="text" name="NAME" value="<?=$Item['NAME'];?>" size="30" maxlength="100" ></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_DATE_START")?></td>
            <td><input type="text" name="DATE_START" value="<?=$Item['DATE_START'];?>" size="30" maxlength="100" ></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_DATE_END")?></td>
            <td><input type="text" name="DATE_END" value="<?=$Item['DATE_END'];?>" size="30" maxlength="100" ></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_ACTIVE")?></td>
            <td><?=InputType("checkbox", "ACTIVE", "Y", '',  '','',$Item['ACTIVE'] ? 'checked':'');?>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_SORT")?></td>
            <td><input type="text" name="SORT" value="<?=$Item['SORT'];?>" size="30" maxlength="100" ></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_TYPE")?></td>
            <td><input type="text" name="TYPE" value="<?=$Item['TYPE'];?>" size="30" maxlength="100" ></td>
        </tr>
        <?
        // завершение формы - вывод кнопок сохранения изменений
        $tabControl->Buttons(
            array(
                "disabled"=>($POST_RIGHT<"W"),
                "back_url"=>"rubric_admin.php?lang=".LANG,
            )
        );
        // завершаем интерфейс закладки
        $tabControl->End();
        $tabControl->ShowWarnings("post_form", $message);
        ?>
        <?echo BeginNote();?>
        <span class="required">*</span><?echo GetMessage("REQUIRED_FIELDS")?>
        <?echo EndNote();?>
    </form>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>