<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $ITEM
 * @var array $TYPE_OPTIONS
 * @var CatalogSectionComponent $component
 */

if($TYPE_OPTIONS['POSTER'])
    $POSTER=CFile::GetFileArray($TYPE_OPTIONS['POSTER']);
?>
<div class="sticker-video">
    <?if(!empty($TYPE_OPTIONS['LINK'])):?>
    <a href="<?=$TYPE_OPTIONS['LINK']?>" class="<?=$TYPE_OPTIONS['LINK_CLASS']?>" <?=$OPTIONS['LINK_ADDITIONAL']?>>
    <?endif;?>
    <video playsinline webkit-playsinline <?=$TYPE_OPTIONS['WIDTH']?'width="'.$TYPE_OPTIONS['WIDTH'].'" ':''?><?=$TYPE_OPTIONS['HEIGHT']?'height="'.$TYPE_OPTIONS['HEIGHT'].'" ':''?><?=$TYPE_OPTIONS['CONTROLS']?'controls ':''?><?=$TYPE_OPTIONS['AUTOPLAY']?'autoplay ':''?><?=$TYPE_OPTIONS['LOOP']?'loop ':''?><?=$TYPE_OPTIONS['MUTED']?'muted ':''?><?=$POSTER['SRC']?'poster="'.$POSTER['SRC'].'"':''?>>
    <?foreach ($TYPE_OPTIONS['VIDEO'] as $video_type=>$file){
        $file=CFile::GetFileArray($file);
        if(empty($file['SRC']))
            continue;

        switch ($video_type){
            case 'MP4':
                ?>
                <source src="<?=$file['SRC']?>" type="<?=$file['CONTENT_TYPE']?>" />
                <?
                break;
            case 'WEBM':
                ?>
                <source src="<?=$file['SRC']?>" type="<?=$file['CONTENT_TYPE']?>" />
                <?
                break;
            case 'OGV':
                ?>
                <source src="<?=$file['SRC']?>" type="<?=$file['CONTENT_TYPE']?>" />
                <?
                break;
        }
    }?>
    </video>
    <?if(!empty($TYPE_OPTIONS['LINK'])):?>
    </a>
    <?endif;?>
    <?if(!empty($TYPE_OPTIONS['HTML'])):?>
        <?=$TYPE_OPTIONS['HTML']?>
    <?endif;?>
</div>