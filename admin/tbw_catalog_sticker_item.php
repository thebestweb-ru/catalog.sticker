<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Bitrix\Main\Type,
    TheBestWeb\CatalogSticker,
    TheBestWeb\CatalogSticker\ItemTable;

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

$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($POST_RIGHT=="D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

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

$sTableID=ItemTable::getTableName();

$aTabs[]=array("DIV" => "edit1", "TAB" => Loc::getMessage($MODULE_LANG_PREFIX."_TAB_MAIN"), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage($MODULE_LANG_PREFIX."_TAB_MAIN"));

$type_sticker=CatalogSticker::GetTypeStickers();
foreach ($type_sticker as $key=>$item){
    $aTabs[]=array("DIV" => $key, "TAB" => Loc::getMessage($MODULE_LANG_PREFIX."_TAB_TYPE",array('#TYPE#'=>$item)), "ICON"=>"main_user_edit", "TITLE"=>Loc::getMessage($MODULE_LANG_PREFIX."_TAB_TYPE",array('#TYPE#'=>$item)));
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);
$LIST_ID = intval($LIST_ID);
$message = null;		// сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

$historyId = 0;
if (isset($_REQUEST['history_id']) && is_string($_REQUEST['history_id']))
    $historyId = (int)$_REQUEST['history_id'];
if ($historyId > 0 && $bBizproc)
    $view = "Y";
else
    $historyId = 0;

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

    if(empty($NAME)){
        $message = new CAdminMessage(array(
            'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS_NAME"),
            'TYPE' => 'ERROR',
            'DETAILS' => '',
            'HTML' => true
        ));
    }

    if(!empty($TYPE_OPTIONS)){
        foreach ($TYPE_OPTIONS as $type_opt=>&$options){
            if(is_array($options)){
                foreach ($options as $opt_key=>$opt_item){
                    if(empty($opt_item))
                        unset($options[$opt_key]);
                }
            }

         /*   if(empty($options)){
                $message = new CAdminMessage(array(
                    'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS"),
                    'TYPE' => 'ERROR',
                    'DETAILS' => '',
                    'HTML' => true
                ));
                break;
            }*/

            switch ($type_opt){
                case 'HTML':

                    if($HTML_VALUE)
                        $options['VALUE']=$HTML_VALUE;

                    if($TYPE==$type_opt){
                        if(empty($options['VALUE'])){
                            $message = new CAdminMessage(array(
                                'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS_TYPE",array('#TYPE#'=>$type_sticker[$type_opt])),
                                'TYPE' => 'ERROR',
                                'DETAILS' => '',
                                'HTML' => true
                            ));
                        }
                    }

                    break;
                case 'PICTURE':
                    foreach ($options as $picture_type=>&$picture_options){
                        if(empty($picture_options)){
                            unset($picture_options);
                            continue;
                        }

                        foreach ($picture_options as $key_opt=>&$option){
                            if(empty($option)){
                                unset($picture_options[$key_opt]);
                                continue;
                            }

                            switch($key_opt){
                                case'IMAGE':
                                    $arImageFileds=$option;

                                    if(is_array($arImageFileds)){
                                        $arImageFileds['MODULE_ID']=$MODULE_ID;
                                        $arImageFileds=CIBlock::makeFileArray($arImageFileds,$TYPE_OPTIONS_del[$type_opt][$picture_type]['IMAGE'],$TYPE_OPTIONS_descr[$type_opt][$picture_type]['IMAGE']);
                                    }elseif(is_numeric($arImageFileds)){
                                        if($TYPE_OPTIONS_del[$type_opt][$picture_type]['IMAGE']){
                                            CFile::Delete($arImageFileds);
                                            $arImageFileds=NULL;
                                            unset($picture_options[$key_opt]);
                                            continue;
                                        }else{
                                            $arImageFileds=CFile::MakeFileArray($arImageFileds);
                                        }
                                    }

                                    if(is_array($arImageFileds)){
                                        if($TYPE_OPTIONS_descr[$type_opt][$picture_type]['IMAGE'])
                                            $arImageFileds['description']=$TYPE_OPTIONS_descr[$type_opt][$picture_type]['IMAGE'];

                                        $result_save_file=CFile::SaveFile($arImageFileds,$MODULE_ID,false,false);
                                    }
                                    if(!$result_save_file)
                                        unset($option);

                                    $option=$result_save_file;
                                    unset($arImageFileds,$result_save_file);
                                    break;
                            }
                        }
                        if(empty($picture_options)){
                            unset($options[$picture_type]);
                        }
                    }
                    if($TYPE==$type_opt){
                        if(empty($options)){
                            unset($TYPE_OPTIONS[$type_opt]);
                            $message = new CAdminMessage(array(
                                'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS_TYPE",array('#TYPE#'=>$type_sticker[$type_opt])),
                                'TYPE' => 'ERROR',
                                'DETAILS' => '',
                                'HTML' => true
                            ));
                        }
                    }

                    break;

            }
        }
    }else{
        $message = new CAdminMessage(array(
            'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS"),
            'TYPE' => 'ERROR',
            'DETAILS' => '',
            'HTML' => true
        ));
    }


    // обработка данных формы
    $arFields = Array(
        "LIST_ID"=> $LIST_ID,
        "NAME"=> $NAME,
        "DATE_START"=> '',
        "DATE_END"=> '',
        "ACTIVE"=> $ACTIVE=='Y'?'Y':'',
        "TYPE"=> $TYPE,
        "TYPE_OPTIONS"=> $TYPE_OPTIONS,
    );

    if(!empty($SORT))
        $arFields['SORT']=intval($SORT);

    if(!empty($DATE_START))
        $arFields['DATE_START']=new Type\DateTime($DATE_START);

    if(!empty($DATE_END))
        $arFields['DATE_END']=new Type\DateTime($DATE_END);


    if(!$message){
        // сохранение данных
        if($action =='add')
        {
            $result = ItemTable::add($arFields);
            if (!$result->isSuccess())
            {
                $message = $result->getErrorMessages();
            }

        }
        else
        {
            $result = ItemTable::update($ID,$arFields);
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
            LocalRedirect("/bitrix/admin/tbw_catalog_sticker_item.php?lang=".LANG."&site=".$SITE_ID."&ID=".$result->getId()."&LIST_ID=".$LIST_ID."&mess=ok&".$tabControl->ActiveTabParam());
        else
            // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
            LocalRedirect("/bitrix/admin/tbw_catalog_sticker_item_list.php?lang=".LANG."&site=".$SITE_ID."&ID=".$LIST_ID."&mess=ok&".$tabControl->ActiveTabParam());
    }
    else
    {
        // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if($e = $APPLICATION->GetException())
            $message = new CAdminMessage(Loc::getMessage($MODULE_LANG_PREFIX."_SAVE_ERROR"), $e);
        $bVarsFromForm = true;
    }
}

// выборка данных
if($ID>0)
{
    $result = ItemTable::GetByID($ID);
    if($Item=$result->fetch()){
        $ID=$Item['ID'];
        $bVarsFromForm = true;

        //debugmessage($Item);
    }
}

$APPLICATION->SetTitle(($ID>0? Loc::getMessage($MODULE_LANG_PREFIX."_TITLE_EDIT").'['.$ID.'] '.$Item['NAME'] : Loc::getMessage($MODULE_LANG_PREFIX."_TITLE_ADD")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

CJSCore::Init(array('date'));

if($_REQUEST["mess"] == "ok" && !empty($SITE_ID))
    CAdminMessage::ShowMessage(array("MESSAGE"=>Loc::getMessage($MODULE_LANG_PREFIX."_SAVE_OK"), "TYPE"=>"OK"));

if($message)
    echo $message->Show();

?>
    <form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="post_form">
        <?echo bitrix_sessid_post();?>
        <input type="hidden" name="lang" value="<?=LANG?>">
        <input type="hidden" name="action" value="<?=$bVarsFromForm ? 'update' : 'add';?>">
        <input type="hidden" name="ID" value="<?=$ID ? $ID : '';?>">
        <input type="hidden" name="site" value="<?=$SITE_ID?>">
        <input type="hidden" name="LIST_ID" value="<?=$Item['LIST_ID'] ? $Item['LIST_ID'] : $LIST_ID;?>">

        <?
        // отобразим заголовки закладок
        $tabControl->Begin();
        //Вкладка ОСНОВНЫЕ
        $tabControl->BeginNextTab();
        ?>
        <tr>
            <td><span class="required">*</span><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_NAME")?></td>
            <td><input type="text" name="NAME" value="<?=$Item['NAME'];?>" size="50" ></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_DATE_START")?></td>
            <td><?echo CAdminCalendar::CalendarDate("DATE_START", $Item['DATE_START'], 19, true)?></td>
        </tr>
        <tr>
            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_FIELD_DATE_END")?></td>
            <td><?echo CAdminCalendar::CalendarDate("DATE_END", $Item['DATE_END'], 19, true)?></td>
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
                echo SelectBoxFromArray("TYPE", array("REFERENCE" => array_values ($type_sticker), "REFERENCE_ID" => array_keys ($type_sticker)),$Item['TYPE'],'','onchange="ChangeTypeOptions(this.value)"');
                ?>
            </td>
        </tr>

        <?foreach ($type_sticker as $key_tab=>$tab_item){
            $tabControl->BeginNextTab();
                switch ($key_tab){
                    case 'HTML':
                        ?>
                        <tr>
                            <td colspan="2" align="center">
                                <?CFileMan::AddHTMLEditorFrame(
                                    "HTML_VALUE",
                                    $Item['TYPE_OPTIONS']['HTML']['VALUE'],
                                    "TYPE_OPTIONS[HTML][TEXT_TYPE]",
                                    $Item['TYPE_OPTIONS']['HTML']['TEXT_TYPE'],
                                    array(
                                        'height' => 450,
                                        'width' => '100%'
                                    ),
                                    "N",
                                    0,
                                    "",
                                    "",
                                    $arIBlock["LID"],
                                    true,
                                    false,
                                    array(
                                        'toolbarConfig' => CFileMan::GetEditorToolbarConfig("iblock_".(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1 ? 'public' : 'admin')),
                                        'saveEditorKey' => $LIST_ID,
                                        'hideTypeSelector' => '',
                                    )
                                );?>
                            </td>
                        </tr>
                        <?php
                        break;
                    case 'PICTURE':
                        ?>
                        <tr id="DESKTOP_PICTURE" class="adm-detail-file-row">
                            <td class="adm-detail-valign-top">Десктоп:</td>
                            <td>
                                <?if($historyId > 0):?>
                                    <?echo CFileInput::Show("TYPE_OPTIONS[PICTURE][DESKTOP][IMAGE]", $Item['TYPE_OPTIONS']['PICTURE']['DESKTOP']['IMAGE'], array(
                                        "IMAGE" => "Y",
                                        "PATH" => "Y",
                                        "FILE_SIZE" => "Y",
                                        "DIMENSIONS" => "Y",
                                        "IMAGE_POPUP" => "Y",
                                    ));
                                    ?>
                                <?else:?>
                                    <?if (class_exists('\Bitrix\Main\UI\FileInput', true))
                                    {
                                        echo \Bitrix\Main\UI\FileInput::createInstance(array(
                                            "name" => "TYPE_OPTIONS[PICTURE][DESKTOP][IMAGE]",
                                            "description" => true,
                                            "upload" => true,
                                            "allowUpload" => "I",
                                            "medialib" => true,
                                            "fileDialog" => true,
                                            "cloud" => true,
                                            "delete" => true,
                                            "maxCount" => 1
                                        ))->show($bVarsFromForm ? $Item['TYPE_OPTIONS']['PICTURE']['DESKTOP']['IMAGE'] : ($ID > 0 && !$bCopy ? $Item['TYPE_OPTIONS']['PICTURE']['DESKTOP']['IMAGE']: 0), $bVarsFromForm);
                                    }
                                    else
                                    {
                                        echo CFileInput::Show("TYPE_OPTIONS[PICTURE][DESKTOP][IMAGE]", ($ID > 0 && !$bCopy ? $Item['TYPE_OPTIONS']['PICTURE']['DESKTOP']['IMAGE']: 0),
                                            array(
                                                "IMAGE" => "Y",
                                                "PATH" => "Y",
                                                "FILE_SIZE" => "Y",
                                                "DIMENSIONS" => "Y",
                                                "IMAGE_POPUP" => "Y",
                                            ), array(
                                                'upload' => true,
                                                'medialib' => true,
                                                'file_dialog' => true,
                                                'cloud' => true,
                                                'del' => true,
                                                'description' => true,
                                            )
                                        );
                                    }
                                    ?>
                                <?endif?>
                            </td>
                        </tr>
                        <tr>
                            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_PICTURE_LINK")?></td>
                            <td><input type="text" name="TYPE_OPTIONS[PICTURE][DESKTOP][LINK]" value="<?=$Item['TYPE_OPTIONS']['PICTURE']['DESKTOP']['LINK'];?>" size="100" ></td>
                        </tr>
                        <tr>
                            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_PICTURE_LINK_CLASS")?></td>
                            <td><input type="text" name="TYPE_OPTIONS[PICTURE][DESKTOP][LINK_CLASS]" value="<?=$Item['TYPE_OPTIONS']['PICTURE']['DESKTOP']['LINK_CLASS'];?>" size="50" ></td>
                        </tr>
                        <tr id="MOBILE_PICTURE" class="adm-detail-file-row">
                            <td  class="adm-detail-valign-top">Мобильная:</td>
                            <td>
                                <?if($historyId > 0):?>
                                    <?echo CFileInput::Show("TYPE_OPTIONS[PICTURE][MOBILE][IMAGE]", $Item['TYPE_OPTIONS']['PICTURE']['MOBILE']['IMAGE'], array(
                                        "IMAGE" => "Y",
                                        "PATH" => "Y",
                                        "FILE_SIZE" => "Y",
                                        "DIMENSIONS" => "Y",
                                        "IMAGE_POPUP" => "Y",
                                    ));
                                    ?>
                                <?else:?>
                                    <?if (class_exists('\Bitrix\Main\UI\FileInput', true))
                                    {
                                        echo \Bitrix\Main\UI\FileInput::createInstance(array(
                                            "name" => "TYPE_OPTIONS[PICTURE][MOBILE][IMAGE]",
                                            "description" => true,
                                            "upload" => true,
                                            "allowUpload" => "I",
                                            "medialib" => true,
                                            "fileDialog" => true,
                                            "cloud" => true,
                                            "delete" => true,
                                            "maxCount" => 1
                                        ))->show($bVarsFromForm ? $Item['TYPE_OPTIONS']['PICTURE']['MOBILE']['IMAGE'] : ($ID > 0 && !$bCopy? $Item['TYPE_OPTIONS']['PICTURE']['MOBILE']['IMAGE']: 0), $bVarsFromForm);
                                    }
                                    else
                                    {
                                        echo CFileInput::Show("TYPE_OPTIONS[PICTURE][MOBILE][IMAGE]", ($ID > 0 && !$bCopy? $Item['TYPE_OPTIONS']['PICTURE']['MOBILE']['IMAGE']: 0),
                                            array(
                                                "IMAGE" => "Y",
                                                "PATH" => "Y",
                                                "FILE_SIZE" => "Y",
                                                "DIMENSIONS" => "Y",
                                                "IMAGE_POPUP" => "Y",
                                            ), array(
                                                'upload' => true,
                                                'medialib' => true,
                                                'file_dialog' => true,
                                                'cloud' => true,
                                                'del' => true,
                                                'description' => true,
                                            )
                                        );
                                    }
                                    ?>
                                <?endif?>
                            </td>
                        </tr>
                        <tr>
                            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_PICTURE_LINK")?></td>
                            <td><input type="text" name="TYPE_OPTIONS[PICTURE][MOBILE][LINK]" value="<?=$Item['TYPE_OPTIONS']['PICTURE']['MOBILE']['LINK'];?>" size="100" ></td>
                        </tr>
                        <tr>
                            <td><?=Loc::getMessage($MODULE_LANG_PREFIX."_PICTURE_LINK_CLASS")?></td>
                            <td><input type="text" name="TYPE_OPTIONS[PICTURE][MOBILE][LINK_CLASS]" value="<?=$Item['TYPE_OPTIONS']['PICTURE']['MOBILE']['LINK_CLASS'];?>" size="50" ></td>
                        </tr>
                        <?php
                        break;
                }
        }
        ?>
        <?
        // завершение формы - вывод кнопок сохранения изменений
        $tabControl->Buttons(
            array(
                "disabled"=>($POST_RIGHT<"W"),
                "back_url"=>"tbw_catalog_sticker_list.php?lang=".LANG."&site=".$SITE_ID."&ID=".$GROUP_ID
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
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>