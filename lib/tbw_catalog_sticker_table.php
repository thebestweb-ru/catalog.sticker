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
     * Returns validators for SITE_ID field.
     *
     * @return array
     */
    public static function validateSiteId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 3),
        );
    }
    /**
     * Returns validators for SEND_TO_CRM field.
     *
     * @return array
     */
    public static function validateSendToCrm()
    {
        return array(
            new Main\Entity\Validator\Length(null, 1),
        );
    }
    /**
     * Returns validators for WISHLIST_CODE field.
     *
     * @return array
     */
    public static function validateWishlistCode()
    {
        return array(
            new Main\Entity\Validator\Length(null, 255),
        );
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            new Entity\StringField('SITE_ID', [
                'required' => true,
                'validation' => array(__CLASS__, 'validateSiteId'),
            ]),
            new Entity\IntegerField('USER_ID',[
            'save_data_modification' => function () {
                return array(
                    function ($value) {
                        return (int) $value;
                    }
                );
            },
            'fetch_data_modification' => function () {
                return array(
                    function ($value) {
                        return (int) $value;
                    }
                );
            }]),
            new Entity\StringField('USER_NAME'),
            new Entity\StringField('USER_EMAIL'),
            new Entity\StringField('USER_PHONE'),
            new Entity\StringField('SEND_TO_CRM',[
                'validation' => array(__CLASS__, 'validateSendToCrm'),
            ]),
            new Entity\StringField('SEND_TO_EMAIL',[
                'validation' => array(__CLASS__, 'validateSendToCrm'),
            ]),
            new Entity\StringField('WISHLIST_CODE',[
                'validation' => array(__CLASS__, 'validateWishlistCode'),
            ]),
            new Entity\DateTimeField('DATE_CREATE', [
                'default_value' => new  Main\Type\DateTime,
                'required' => true,
            ]),
            new Entity\DateTimeField('DATE_CHANGE', [
                'default_value' => new  Main\Type\DateTime,
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return new  Main\Type\DateTime;
                        }
                    ];
                },
            ]),
            new Entity\TextField('ITEMS', [
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return json_encode($value,JSON_UNESCAPED_UNICODE);
                        }
                    ];
                },
                'fetch_data_modification' => function () {
                    return [
                        function ($value) {
                            return json_decode(htmlspecialchars_decode($value),true);
                        }
                    ];
                }
            ]),
        );
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
     * Returns validators for SITE_ID field.
     *
     * @return array
     */
    public static function validateSiteId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 3),
        );
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true
            )),
            new Entity\StringField('SITE_ID', [
                'required' => true,
                'validation' => array(__CLASS__, 'validateSiteId'),
            ]),
            new Entity\TextField('SETTINGS', [
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return json_encode($value,JSON_UNESCAPED_UNICODE);
                        }
                    ];
                },
                'fetch_data_modification' => function () {
                    return [
                        function ($value) {
                            return json_decode(htmlspecialchars_decode($value),true);
                        }
                    ];
                }
            ])
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
     * Returns validators for IBLOCK_ID field.
     *
     * @return array
     */
    public static function validateIblockId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 11),
        );
    }
    /**
     * Returns validators for ELEMENT_ID field.
     *
     * @return array
     */
    public static function validateElementId()
    {
        return array(
            new Main\Entity\Validator\Length(null, 11),
        );
    }

    /**
     * Returns entity map definition.
     *
     * @return array
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
            new Entity\IntegerField('IBLOCK_ID', [
                'required' => true,
                'validation' => array(__CLASS__, 'validateIblockId'),
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
            new Entity\IntegerField('ELEMENT_ID', [
                'required' => true,
                'validation' => array(__CLASS__, 'validateElementId'),
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
            ])
        );
    }
}