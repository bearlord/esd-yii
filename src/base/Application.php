<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2019/10/11
 * Time: 16:08
 */

namespace ESD\Yii\Base;


use DI\Container;
use ESD\Core\DI\DI;
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
        $params = Server::$instance->getConfigContext()->get('esd-yii.db');

        $containerKey = 'esd-yii.db';
        $db = null;

        //判断ESD的容器是否有connection对象
        if (DI::getInstance()->getContainer()->has($containerKey)) {
            $db = DI::getInstance()->getContainer()->get($containerKey);
            return $db;
        }

        //如果容器没有connection对象，借由Yii容器创建，按key值存入ESD的容器
        $db = Yii::createObject($params);
        $db->open();
        DI::getInstance()->getContainer()->set($containerKey, $db);

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