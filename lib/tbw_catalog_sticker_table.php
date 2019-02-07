<?php
namespace TBW\CatalogSticker;

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
            new Entity\StringField('NAME',[
                'required' => true,
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(3, 255),
                    );
                },
            ]),
            new Entity\DateField('DATE_START',[
                'data_type' => 'datetime',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Date()
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            if($value)
                                return $value->toString();

                            return $value;
                        }
                    );
                }
            ]),
            new Entity\DateField('DATE_END',[
                'data_type' => 'datetime',
                'validation' => function () {
                    return array(
                         new Entity\Validator\Date()
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            if($value)
                                return $value->toString();

                            return $value;
                        }
                    );
                }
            ]),
            new Entity\StringField('ACTIVE',[
                'data_type' => 'boolean',
                'values' => array('N','Y'),
                'default_value' => 'Y',
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(null, 1),
                    );
                },
            ]),
            new Entity\IntegerField('SORT',[
                'default_value' => 500,
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
            new Entity\IntegerField('LIST_ID', array(
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
            new Entity\IntegerField('IBLOCK_ID', array(
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
            new Entity\IntegerField('SECTION_ID', array(
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
            new Entity\StringField('NAME', [
                'required' => true,
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(3, 255),
                    );
                },
            ]),
            new Entity\DateField('DATE_START',[
                'data_type' => 'datetime',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Date()
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            if($value)
                                return $value->toString();

                            return $value;
                        }
                    );
                }
            ]),
            new Entity\DateField('DATE_END',[
                'data_type' => 'datetime',
                'validation' => function () {
                    return array(
                        new Entity\Validator\Date()
                    );
                },
                'fetch_data_modification' => function () {
                    return array(
                        function ($value) {
                            if($value)
                                return $value->toString();

                            return $value;
                        }
                    );
                }
            ]),
            new Entity\StringField('ACTIVE',[
                'validation' => function () {
                    return array(
                        new Main\Entity\Validator\Length(null, 1),
                    );
                },
            ]),
            new Entity\IntegerField('SORT',[
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