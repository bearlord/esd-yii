<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2019/10/11
 * Time: 16:08
 */

namespace ESD\Yii\Base;


use DI\Container;
use ESD\Core\Server\Server;
use ESD\Yii\Db\Connection;

class Application
{
    /**
     * @var static[] static instances in format: `[className => object]`
     */
    private static $_instances = [];

    /**
     * Returns static class instance, which can be used to obtain meta information.
     * @param bool $refresh whether to re-create static instance even, if it is already cached.
     * @return static class instance.
     */
    public static function instance($refresh = false)
    {
        $className = get_called_class();
        if ($refresh || !isset(self::$_instances[$className])) {
            self::$_instances[$className] = Yii::createObject($className);
        }
        return self::$_instances[$className];
    }

    public function getDb()
    {
        /*
        $db = new Connection();
        $db->dsn = "pgsql:host=192.168.108.130;dbname=sd_test";
        $db->username = 'postgres';
        $db->password = '123456';
        return $db;
        */

        $dbConfig = Server::$instance->getConfigContext()->get('esd-yii.db');

        $params = array_merge([
            'class' => \ESD\Yii\Db\Connection::class
        ], $dbConfig);

        $db = Yii::createObject($params);
        return $db;
    }

    /**
     * Returns the internationalization (i18n) component
     * @return \ESD\Yii\I18n\I18N the internationalization application component.
     */
    public function getI18n()
    {
        $i18n = Yii::createObject([
            'class' => \ESD\Yii\i18n\I18N::class
        ]);

        return $i18n;
    }
}