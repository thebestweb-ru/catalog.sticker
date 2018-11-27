<?php
namespace TheBestWeb;


class CatalogSticker
{

    public function GetTypeStickers(){
        return array(
            'POSITIONS'=>'Позиционное',
            'FIXED'=>'Фиксированное',
            'FIXED_POSITIONS'=>'Фиксированное чередование',
        );
    }
}