<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    TheBestWeb\CatalogSticker,
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

$SITE_ID=$_REQUEST['site'] ? $_REQUEST['site'] : null;
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
$default_type_keys=array_keys (CatalogSticker::GetTypeStickers());
$default_type=$default_type_keys[0];

// выборка данных
if($ID>0 )
{
    $result = ListTable::GetById($ID);
    if($Item=$result->fetch()){
        $ID=$Item['ID'];
        $bVarsFromForm = true;
        $default_type=$Item['TYPE'];
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
        <input type="hidden" name="site" value="<?=$SITE_ID?>">
        <input type="hidden" name="action" value="<?=$bVarsFromForm ? 'update' : 'add';?>">
        <input type="hidden" name="ID" value="<?=$ID ? $ID : '';?>">

        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        CJSCore::Init(array('date'));
        ?>
        <?if(!empty($arSites)):?>
            <?foreach ($arSites as $lid=>$site_name):?>

            <?endforeach;?>
        <?endif;?>
        <tr>
            <td><span class="required">*</span><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_SITE_ID")?></td>
            <td><label><?=$Item['SITE_ID'] ? $Item['SITE_ID'] : $SITE_ID;?></label></td>
        </tr>
        <tr>
            <td><span class="required">*</span><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_NAME")?></td>
            <td><input type="text" name="NAME" value="<?=$Item['NAME'];?>" size="50" ></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_DATE_START")?></td>
            <td><?echo CAdminCalendar::CalendarDate("ACTIVE_START", $Item['ACTIVE_START'], 19, true)?></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_DATE_END")?></td>
            <td><?echo CAdminCalendar::CalendarDate("ACTIVE_END", $Item['ACTIVE_END'], 19, true)?></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_ACTIVE")?></td>
            <td>
                <?if(isset($Item['ACTIVE'])):?>
                    <?=InputType("checkbox", "ACTIVE", "Y", '',  '','',$Item['ACTIVE'] ? 'checked':'');?>
                <?else:?>
                    <?=InputType("checkbox", "ACTIVE", "Y", '',  '','','checked');?>
                <?endif;?>
            </td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_SORT")?></td>
            <td><input type="text" name="SORT" value="<?=$Item['SORT'];?>" size="20"></td>
        </tr>
        <tr>
            <td><span class="required">*</span><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_TYPE")?></td>
            <td>
                <?
                echo SelectBoxFromArray("TYPE", array("REFERENCE" => array_values (CatalogSticker::GetTypeStickers()), "REFERENCE_ID" => array_keys (CatalogSticker::GetTypeStickers())),$Item['TYPE'],'','onchange="ChangeTypeOptions(this.value)"');
                ?>
            </td>
        </tr>
        <tr id="type-options">
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
        <span class="required">*</span><?echo Loc::getMessage("REQUIRED_FIELDS")?>
        <?echo EndNote();?>
    </form>
<script>
    ChangeTypeOptions('<?=CUtil::JSEscape($default_type)?>');
    function ChangeTypeOptions(type){
        var actionUrl = '/bitrix/admin/tbw_catalog_sticker_list_type_options.php?lang=' + BX.message('LANGUAGE_ID');
        var data=[];
        data['ID'] = '<?=CUtil::JSEscape($ID)?>';
        data['TYPE'] = type;
        data['sessid'] = BX.bitrix_sessid();
        data = BX.ajax.prepareData(data);

        BX.ajax({
            method: 'POST',
            dataType: 'html',
            url: actionUrl,
            data:  data,
            onsuccess: function(data){
                BX('type-options').innerHTML=data;
            }
        });
    }

</script>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>