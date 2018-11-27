<?php
namespace TheBestWeb\CatalogSticker;

use Bitrix\Main,
    Bitrix\Main\Entity;

class ListTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'tbw_catalog_sticker_list';
    }

    /**
     * @return array
     * @throws Main\ObjectException
     * @throws Main\SystemException
     */
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            ]),
            new Entity\StringField('SITE_ID', [
                'required' => true,
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(null, 3),
                    );
                },
            ]),
            new Entity\IntegerField('NAME',[
                'required' => true,
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(3, 255),
                    );
                },
            ]),
            new Entity\StringField('DATE_START',[
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Date(),
                    );
                },
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return new  Main\Type\DateTime;
                        }
                    ];
                },
            ]),
            new Entity\StringField('DATE_END',[
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Date(),
                    );
                },
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return new  Main\Type\DateTime;
                        }
                    ];
                },
            ]),
            new Entity\StringField('ACTIVE',[
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(null, 1),
                    );
                },
            ]),
            new Entity\StringField('SORT',[
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            ]),
            new Entity\StringField('TYPE',[
                'required' => true,
            ]),
            new Entity\StringField('TYPE_OPTIONS',[
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return json_encode($value,JSON_UNESCAPED_UNICODE);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return json_decode(htmlspecialchars_decode($value),true);
                        }
                    );
                }
            ]),
            new Entity\DateTimeField('LIST_SECTIONS_ID', [
                'required' => true,
            ]),
        ];
    }
}
class ListSectionsTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'tbw_catalog_sticker_list_sections';
    }

    /**
     * @return array
     * @throws Main\SystemException
     */
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            )),
            new Entity\StringField('LIST_ID', array(
                'required' => true,
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            )),
            new Entity\StringField('IBLOCK_ID', array(
                'required' => true,
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            )),
            new Entity\StringField('SECTION_ID', array(
                'required' => true,
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            )),
            new Entity\StringField('TROUGHT_SECTION', array(
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(null, 1),
                    );
                },
            )),
        );
    }
}
class ItemTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'tbw_catalog_sticker_item';
    }


    /**
     * @return array
     * @throws Main\SystemException
     */
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            )),
            new Entity\IntegerField('LIST_ID', [
                'required' => true,
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            ]),
            new Entity\IntegerField('NAME', [
                'required' => true,
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(3, 255),
                    );
                },
            ]),new Entity\StringField('DATE_START',[
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Date(),
                    );
                },
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return new  Main\Type\DateTime;
                        }
                    ];
                },
            ]),
            new Entity\StringField('DATE_END',[
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Date(),
                    );
                },
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return new  Main\Type\DateTime;
                        }
                    ];
                },
            ]),
            new Entity\StringField('ACTIVE',[
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(null, 1),
                    );
                },
            ]),
            new Entity\StringField('SORT',[
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return intval($value);
                        }
                    );
                }
            ]),
            new Entity\StringField('TYPE',[
                'required' => true,
            ]),
            new Entity\StringField('TYPE_OPTIONS',[
                'save_data_modification' => function () {
                    return array(
                        function ($value) {
                            return json_encode($value,JSON_UNESCAPED_UNICODE);
                        }
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            return json_decode(htmlspecialchars_decode($value),true);
                        }
                    );
                }
            ]),
        );
    }
}