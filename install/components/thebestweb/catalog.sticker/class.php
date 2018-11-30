<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use TheBestWeb\CatalogSticker;
use TheBestWeb\CatalogSticker\ListTable;
use TheBestWeb\CatalogSticker\ListSectionsTable;
use TheBestWeb\CatalogSticker\ItemTable;

class CCatalogSticker extends CBitrixComponent
{
    protected $errors = array();
    protected $site_id;
    protected $StickerList;
    protected $ShowStickerList;

    var $MODULE_ID = 'thebestweb.catalog.sticker';
    var $MODULE_LANG_PREFIX = 'TBW_CATALOG_STICKER';

    public function onIncludeComponentLang()
    {
        Loc::loadMessages(__FILE__);
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['CACHE_TIME'] = isset($arParams['CACHE_TIME']) ?$arParams['CACHE_TIME']: 36000000;
        $arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
        $arParams['SECTION_ID'] = intval($arParams['SECTION_ID']);

        return $arParams;
    }

    public function executeComponent()
    {
        try
        {
            $this->checkModules();
            $this->getResult();
            $this->includeComponentTemplate();
        }
        catch (SystemException $e)
        {
            ShowError($e->getMessage());
        }
    }

    protected function checkModules()
    {
        if (!Loader::includeModule($this->MODULE_ID))
            throw new SystemException(Loc::getMessage($this->MODULE_LANG_PREFIX.'_NOT_INSTALL'));

        if (!Loader::includeModule('iblock'))
            throw new SystemException(Loc::getMessage($this->MODULE_LANG_PREFIX.'_IBLOCK_NOT_INSTALL'));
    }

    protected function prepareDate(&$arItem) {

    }

    protected function getResult()
    {
        global $USER;

        if ($this->errors)
            throw new SystemException(current($this->errors));

        $arParams = $this->arParams;

        $cacheId = serialize($this->arParams);
        $cacheId .= $USER->GetGroups();

        $cache = Bitrix\Main\Data\Cache::createInstance();
        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheId, $this->MODULE_ID))
        {
            $result = $cache->getVars();
        }
        elseif ($cache->startDataCache())
        {
            $arSections=array();
            $nav = CIBlockSection::GetNavChain($arParams['IBLOCK_ID'], $arParams['SECTION_ID'],array("ID","CODE","NAME","DEPTH_LEVEL"));
            while($nav_Chain = $nav->Fetch()){
                $arSections[intval($nav_Chain['ID'])]=$nav_Chain;
            }
            if(empty($arSections)){
                $cache->abortDataCache();
                return false;
            }

            $FilterStickerList=array(
                'SITE_ID'=>SITE_ID,
                'ACTIVE'=>'Y',
                array(
                    "LOGIC" => "OR",
                    array("DATE_START"=>false,"DATE_END"=>false),
                    array('<=DATE_START'=>new DateTime()),
                    array('>=DATE_END'=>new DateTime()),
                ),

            );
            $rsStickerList=ListTable::getList(array('order'=>['SORT'=>ASC],'filter'=>$FilterStickerList));
            while($arStickerList=$rsStickerList->Fetch()){
                $arStickerList['SECTIONS']=array();
                $FilterStickerListSections=array(
                    'LIST_ID'=>$arStickerList['ID'],
                    'IBLOCK_ID'=>$arParams['IBLOCK_ID'],
                    'SECTION_ID'=>array_keys($arSections)
                );
                $rsStickerListSections=ListSectionsTable::getList(array('order'=>['SECTION_ID'=>ASC],'filter'=>$FilterStickerListSections));
                while($arStickerListSections=$rsStickerListSections->Fetch()){
                    $arStickerList['SECTIONS'][$arStickerListSections['SECTION_ID']]=$arStickerListSections;
                }
                $this->StickerList[$arStickerList['ID']]=$arStickerList;
            }
            if(empty($this->StickerList)){
                $cache->abortDataCache();
                return false;
            }


            $result=array();
            foreach ($arSections as $section_id=>$section){
                foreach ($this->StickerList as $list_id=>$list){
                    if(array_key_exists($section_id,$list['SECTIONS'])){
                        if($list['SECTIONS'][$section_id]['TROUGHT_SECTION']=='Y'){
                            $result=array();
                            $result=$list;
                        }elseif($arParams['SECTION_ID']==$section_id && empty($result)){
                            $result=$list;
                        }

                    }
                }
            }

            if(empty($result)){
                $cache->abortDataCache();
                return false;
            }

            $result['ITEMS']=array();

            $FilterStickerItems=array(
                'LIST_ID'=>$result['ID'],
                'ACTIVE'=>'Y',
                array(
                    "LOGIC" => "OR",
                    array("DATE_START"=>false,"DATE_END"=>false),
                    array('<=DATE_START'=>new DateTime()),
                    array('>=DATE_END'=>new DateTime()),
                ),
            );
            $rsStickerItems=ItemTable::getList(array('order'=>['SORT'=>ASC],'filter'=>$FilterStickerItems));
            while($arStickerItems=$rsStickerItems->Fetch()){
                $result['ITEMS'][]=$arStickerItems;
            }

            $cache->endDataCache($result);
        }

        $this->arResult = $result;

        return $this;
    }
}