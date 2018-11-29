<?php
namespace TheBestWeb;


class CatalogSticker
{

    public function GetTypeGroupStickers(){
        return array(
            'POSITIONS'=>'Позиционное',
            'FIXED'=>'Фиксированное',
            'FIXED_POSITIONS'=>'Фиксированное чередование',
        );
    }
    public function GetTypeStickers(){
        return array(
            'HTML'=>'HTML Блок',
            'PICTURE'=>'Картинка',
            'VIDEO'=>'Видео',
            'PRODUCT'=>'Товар',
        );
    }
}