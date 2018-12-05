<?
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Bitrix\Main\Type,
    Bitrix\Catalog\CatalogIblockTable,
    Bitrix\Iblock\IblockTable,
    TheBestWeb\CCatalogSticker,
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
if (!Loader::includeModule('catalog'))
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

$IBLOCK_IDS=array();
$rsCatalog= CatalogIblockTable::GetList();
while ($arCatalog=$rsCatalog->Fetch()){
    if(intval($arCatalog['PRODUCT_IBLOCK_ID'])>0)
        continue;

    $rsCatalogIblock=\Bitrix\Iblock\IblockSiteTable::GetList(array('filter'=>['IBLOCK_ID'=>$arCatalog['IBLOCK_ID'],'SITE_ID'=>$SITE_ID]));
    if($arCatalogIblock=$rsCatalogIblock->Fetch()){
        $rsIblockTable=IblockTable::GetList(array('filter'=>['ID'=>$arCatalogIblock['IBLOCK_ID'],'ACTIVE'=>'Y']));
        if($arIblockTable=$rsIblockTable->Fetch()){
            $IBLOCK_IDS[$arIblockTable['ID']]=$arIblockTable['NAME'];
        }
    }
}
if(empty($IBLOCK_IDS)){
    $APPLICATION->ThrowException(Loc::getMessage($MODULE_LANG_PREFIX."_NOT_FIND_IBLOCK_CATALOG"));
    return false;
}
$js_default_ids=array_keys($IBLOCK_IDS);
$js_default_id=$js_default_ids[0];


if($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["AJAX"]=='Y' && !empty($_REQUEST['TYPE'])) {
    require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/prolog_admin_js.php");
    $iblock_id=intval($_REQUEST['IBLOCK_ID']);
    $postfix=rand();
    switch($_REQUEST['TYPE']){

        case 'add':
            ?>
            <tr id="iblock-row-<?=$iblock_id?>-<?=$postfix?>">
                <td>
                    <?=SelectBoxFromArray("", array("REFERENCE" => array_values ($IBLOCK_IDS), "REFERENCE_ID" => array_keys ($IBLOCK_IDS)),$iblock_id,'','onchange="ChangeIblock(this.value,\'iblock-sections-'.$iblock_id.'-'.$postfix.'\')"');?>
                </td>
                <td id="iblock-sections-<?=$iblock_id?>-<?=$postfix?>">
                    <?
                    $l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>intval($_REQUEST['IBLOCK_ID'])), array("ID", "NAME", "DEPTH_LEVEL"));
                    ?>
                    <select name="SECTIONS[<?=$iblock_id?>][]"  onchange="SetTroughtName(this)">
                        <option value=""><?=Loc::getMessage($MODULE_LANG_PREFIX."NOT_SELECTED")?></option>
                        <?
                        while($ar_l = $l->GetNext()):
                            ?><option value="<?echo $ar_l["ID"]?>"><?echo str_repeat(" . ", $ar_l["DEPTH_LEVEL"])?><?echo $ar_l["NAME"]?></option><?
                        endwhile;
                        ?>
                    </select>
                </td>
                <td>
                    <?=InputType("checkbox", "TROUGHT_SECTIONS[".$iblock_id."]", "Y", '',  '','','data-base-name="TROUGHT_SECTIONS['.$iblock_id.']"','');?>
                </td>
            </tr>
            <?
            break;
        case 'change':
            $l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>intval($_REQUEST['IBLOCK_ID'])), array("ID", "NAME", "DEPTH_LEVEL"));
            ?>
            <select name="SECTIONS[<?=$iblock_id?>][]" onchange="SetTroughtName(this)">
                <option value=""><?=Loc::getMessage($MODULE_LANG_PREFIX."NOT_SELECTED")?></option>
                <?
                while($ar_l = $l->GetNext()):
                    ?><option value="<?echo $ar_l["ID"]?>"><?echo str_repeat(" . ", $ar_l["DEPTH_LEVEL"])?><?echo $ar_l["NAME"]?></option><?
                endwhile;
                ?>
            </select>
            <?
            break;
    }

    require($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/include/epilog_admin_js.php");
    exit;
}


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


    if(empty($SITE_ID)){
        $message = new CAdminMessage(array(
            'MESSAGE' =>  Loc::getMessage($MODULE_LANG_PREFIX."_NOT_FIND_SITE_ID"),
            'TYPE' => 'ERROR',
            'DETAILS' => '',
            'HTML' => true
        ));
    }
    if(empty($NAME)){
        $message = new CAdminMessage(array(
            'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS_NAME"),
            'TYPE' => 'ERROR',
            'DETAILS' => '',
            'HTML' => true
        ));
    }
    if(!empty($TYPE_OPTIONS)){
        foreach ($TYPE_OPTIONS as $type=>&$options){
            if(is_array($options)){
                foreach ($options as $opt_key=>$opt_item){
                    if(empty($opt_item))
                        unset($options[$opt_key]);
                }
            }
            if(empty($options)){
                $message = new CAdminMessage(array(
                    'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS"),
                    'TYPE' => 'ERROR',
                    'DETAILS' => '',
                    'HTML' => true
                ));
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

    if(empty($SECTIONS)){
        $message = new CAdminMessage(array(
            'MESSAGE' => Loc::getMessage($MODULE_LANG_PREFIX."_NOT_REQUIRED_FIELDS"),
            'TYPE' => 'ERROR',
            'DETAILS' => '',
            'HTML' => true
        ));
    }

    // обработка данных формы
    $arFields = Array(
        "SITE_ID"=> $SITE_ID,
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
            $result = ListTable::add($arFields);
            if (!$result->isSuccess())
            {
                $message = $result->getErrorMessages();
            }

        }
        else
        {
            $result = ListTable::update($ID,$arFields);
            if (!$result->isSuccess())
            {
                $message = $result->getErrorMessages();
            }
        }
        if(!$message){

            $rsListSections=ListSectionsTable::getList(array('filter'=>['LIST_ID'=>$result->getId()]));
            while($arListSections=$rsListSections->Fetch()){
                ListSectionsTable::delete($arListSections['ID']);
            }

            foreach ($SECTIONS as $_IBLOCK_ID=>$ITEMS_SECTIONS){
                foreach ($ITEMS_SECTIONS as $section_id){
                    if(empty($section_id))
                        continue;

                    $_arFields=array(
                        'LIST_ID'=>$result->getId(),
                        'IBLOCK_ID'=>intval($_IBLOCK_ID),
                        'SECTION_ID'=>intval($section_id),
                        'TROUGHT_SECTION'=>''
                    );
                    if(array_key_exists($section_id,$TROUGHT_SECTIONS[$_IBLOCK_ID]))
                        $_arFields['TROUGHT_SECTION']='Y';

                    $_result = ListSectionsTable::add($_arFields);
                    if (!$_result->isSuccess())
                    {
                        $message = $_result->getErrorMessages();
                    }
                    unset($_arFields);
                }
            }
        }
    }

    if($result)
    {
        // если сохранение прошло удачно - перенаправим на новую страницу
        // (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
        if ($apply != "")
            // если была нажата кнопка "Применить" - отправляем обратно на форму.
            LocalRedirect("/bitrix/admin/tbw_catalog_sticker_list_item.php?lang=".LANG."&site=".$SITE_ID."&ID=".$result->getId()."&mess=ok&".$tabControl->ActiveTabParam());
        else
            // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
            LocalRedirect("/bitrix/admin/tbw_catalog_sticker_list.php?lang=".LANG."&site=".$SITE_ID."&ID=".$result->getId()."&mess=ok&".$tabControl->ActiveTabParam());
    }
    else
    {
        // если в процессе сохранения возникли ошибки - получаем текст ошибки и меняем вышеопределённые переменные
        if($e = $APPLICATION->GetException())
            $message = new CAdminMessage(Loc::getMessage($MODULE_LANG_PREFIX."_SAVE_ERROR"), $e);
        $bVarsFromForm = true;
    }
}
$default_type_keys=array_keys (CCatalogSticker::GetTypeGroupStickers());
$default_type=$default_type_keys[0];

// выборка данных
if($ID>0 )
{
    $result = ListTable::GetById($ID);
    if($Item=$result->fetch()){
        $ID=$Item['ID'];
        $bVarsFromForm = true;
        $default_type=$Item['TYPE'];

        $Item_sections=array();
        $rsSections=ListSectionsTable::getList(array('filter'=>['LIST_ID'=>$ID]));
        while($arSections=$rsSections->Fetch()){
            $Item_sections[]=$arSections;
        }

    }
}

$APPLICATION->SetTitle(($ID>0? Loc::getMessage($MODULE_LANG_PREFIX."_TITLE_EDIT").'['.$ID.'] '.$Item['NAME'] : Loc::getMessage($MODULE_LANG_PREFIX."_TITLE_ADD")));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($_REQUEST["mess"] == "ok" && !empty($SITE_ID))
    CAdminMessage::ShowMessage(array("MESSAGE"=>Loc::getMessage($MODULE_LANG_PREFIX."_SAVE_OK"), "TYPE"=>"OK"));

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
                echo SelectBoxFromArray("TYPE", array("REFERENCE" => array_values (CCatalogSticker::GetTypeGroupStickers()), "REFERENCE_ID" => array_keys (CCatalogSticker::GetTypeGroupStickers())),$Item['TYPE'],'','onchange="ChangeTypeOptions(this.value)"');
                ?>
            </td>
        </tr>
        <tr id="type-options">
        </tr>
        <tr>
            <td></td>
            <td>
                <table border="0" cellpadding="1" cellspacing="1" id="iblock-sections">
                    <tbody>
                    <tr>
                        <td>
                            <b>Инфоблок</b>
                        </td>
                        <td>
                            <b>Раздел</b>
                        </td>
                        <td>
                            <b>Сквозной показ в подразделах</b>
                        </td>
                    </tr>
                    <?if(!empty($Item_sections)):?>
                        <?foreach ($Item_sections as $section):?>
                            <tr id="iblock-row-<?=$section['IBLOCK_ID']?>-<?=$section['ID']?>">
                                <td>
                                    <?=SelectBoxFromArray("", array("REFERENCE" => array_values ($IBLOCK_IDS), "REFERENCE_ID" => array_keys ($IBLOCK_IDS)),$section['IBLOCK_ID'],'','onchange="ChangeIblock(this.value,\'iblock-sections-'.$section['IBLOCK_ID'].'-'.$section['ID'].'\')"');?>
                                </td>
                                <td id="iblock-sections-<?=$section['IBLOCK_ID']?>-<?=$section['ID']?>">
                                    <?
                                    $l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>intval($section['IBLOCK_ID'])), array("ID", "NAME", "DEPTH_LEVEL"));
                                    ?>
                                    <select name="SECTIONS[<?=$section['IBLOCK_ID']?>][]"  onchange="SetTroughtName(this)">
                                        <option value=""><?=Loc::getMessage($MODULE_LANG_PREFIX."NOT_SELECTED")?></option>
                                        <?
                                        while($ar_l = $l->GetNext()):
                                            ?><option value="<?echo $ar_l["ID"]?>" <?=$ar_l["ID"]==$section['SECTION_ID'] ? 'selected':''?> ><?echo str_repeat(" . ", $ar_l["DEPTH_LEVEL"])?><?echo $ar_l["NAME"]?></option><?
                                        endwhile;
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <?=InputType("checkbox", "TROUGHT_SECTIONS[".$section['IBLOCK_ID']."][".$section['SECTION_ID']."]", "Y", $section['TROUGHT_SECTION'],  '','', 'data-base-name="TROUGHT_SECTIONS['.$section['IBLOCK_ID'].']"','');?>
                                </td>
                            </tr>
                        <?endforeach;?>
                    <?endif;?>
                    <tr id="iblock-row">
                    </tr>
                    </tbody>
                </table>
                <input type="button" value="<?=GetMessage("USER_TYPE_PROP_ADD")?>" onclick="AddIblockRow('<?=CUtil::JSEscape($js_default_id)?>', 'iblock-sections')">
            </td>

        </tr>
        <?
        // завершение формы - вывод кнопок сохранения изменений
        $tabControl->Buttons(
            array(
                "disabled"=>($POST_RIGHT<"W"),
                "back_url"=>"tbw_catalog_sticker_list.php?lang=".LANG."&site=".$SITE_ID,
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
    AddIblockRow('<?=CUtil::JSEscape($js_default_id)?>', 'iblock-sections');

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
    function ChangeIblock(iblock_id, node_id) {
        var actionUrl = '<?=CUtil::JSEscape( $APPLICATION->GetCurPage())?>';
        var data=[];
        data['site'] = '<?=CUtil::JSEscape( $SITE_ID)?>';
        data['IBLOCK_ID'] = iblock_id;
        data['AJAX'] = 'Y';
        data['TYPE'] = 'change';
        data['sessid'] = BX.bitrix_sessid();
        data = BX.ajax.prepareData(data);
        BX.ajax({
            method: 'POST',
            dataType: 'html',
            url: actionUrl,
            data:  data,
            onsuccess: function(data){
                BX(node_id).innerHTML=data;
            }
        });
    }
    function AddIblockRow(iblock_id, table_id) {
        var actionUrl = '<?=CUtil::JSEscape( $APPLICATION->GetCurPage())?>';
        var data=[];
        data['site'] = '<?=CUtil::JSEscape( $SITE_ID)?>';
        data['IBLOCK_ID'] = iblock_id;
        data['AJAX'] = 'Y';
        data['TYPE'] = 'add';
        data['sessid'] = BX.bitrix_sessid();
        data = BX.ajax.prepareData(data);
        var tbody = document.getElementById(table_id).getElementsByTagName("tbody")[0];
        BX.ajax({
            method: 'POST',
            dataType: 'html',
            url: actionUrl,
            data:  data,
            onsuccess: function(data){
                tbody.insertAdjacentHTML('beforeEnd', data);
            }
        });
    }
    function SetTroughtName(element) {
        var selectedOption = element.options[element.selectedIndex].value;
        var row=BX.findParent(BX(element),{"tag" : "tr"},true);
        var checkbox=BX.findChild(BX(row),{"tag" : "input"},true);
        if(checkbox)
            checkbox.name =checkbox.getAttribute('data-base-name')+'['+selectedOption+']';
    }
</script>
<? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>