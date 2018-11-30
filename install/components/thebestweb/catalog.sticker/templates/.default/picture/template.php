<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc;

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $ITEM
 * @var array $TYPE_OPTIONS
 * @var CatalogSectionComponent $component
 */

//debugmessage($ITEM);
//debugmessage($TYPE_OPTIONS);
?>
<div class="sticker-picture">
    <div class="uk-card uk-card-default">
        <?
        foreach ($TYPE_OPTIONS as $KEY_TYPE=>$OPTIONS){
            switch($KEY_TYPE){
                case 'DESKTOP':
                    $IMG=CFile::GetFileArray($OPTIONS['IMAGE']);
                    ?>
                    <?if(!empty($OPTIONS['LINK'])):?>
                        <a href="<?=$OPTIONS['LINK']?>" class="<?=$OPTIONS['LINK_CLASS']?>">
                    <?endif;?>
                        <img src="<?=$IMG['SRC']?>" class="uk-responsive-width uk-responsive-height" alt="<?=$IMG['DESCRIPTION']?>" title="<?=$IMG['DESCRIPTION']?>">
                    <?if(!empty($OPTIONS['LINK'])):?>
                        </a>
                    <?endif;?>
                    <?
                    break;
                case 'MOBILE':
                    $IMG=CFile::GetFileArray($OPTIONS['IMAGE']);
                    ?>
                    <?if(!empty($OPTIONS['LINK'])):?>
                        <a href="<?=$OPTIONS['LINK']?>" class="<?=$OPTIONS['LINK_CLASS']?>">
                    <?endif;?>
                        <img src="<?=$IMG['SRC']?>" class="uk-responsive-width uk-responsive-height" alt="<?=$IMG['DESCRIPTION']?>" title="<?=$IMG['DESCRIPTION']?>">
                    <?if(!empty($OPTIONS['LINK'])):?>
                        </a>
                    <?endif;?>
                        <?
                    break;
            }
        }
        ?>
    </div>
</div>