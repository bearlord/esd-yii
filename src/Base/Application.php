<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2019/10/11
 * Time: 16:08
 */

namespace ESD\Yii\Base;

use ESD\Yii\Yii;
use DI\Container;
use ESD\Core\DI\DI;
use ESD\Core\Server\Server;
use ESD\Yii\Db\Connection;
use ESD\Yii\PdoPlugin\PdoPools;

class Application
{
    /**
     * @var string the charset currently used for the application.
     */
    public $charset = 'UTF-8';
    /**
     * @var string the language that is meant to be used for end users. It is recommended that you
     * use [IETF language tags](http://en.wikipedia.org/wiki/IETF_language_tag). For example, `en` stands
     * for English, while `en-US` stands for English (United States).
     * @see sourceLanguage
     */
    public $language = 'en-US';

    /**
     * @var string the language that the application is written in. This mainly refers to
     * the language that the messages and view files are written in.
     * @see language
     */
    public $sourceLanguage = 'en-US';

    /**
     * @var static[] static instances in format: `[className => object]`
     */
    private static $_instances = [];

    public function __construct()
    {
        Yii::$app = $this;
        $this->preInit();
    }

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

    /**
     * Prepare init
     */
    public function preInit()
    {
        $config = Server::$instance->getConfigContext()->get('esd-yii');
        if (!empty($config['language'])) {
            $this->language = $config['language'];
        }
    }

    public function getDb()
    {
        $name = 'default';
        $db = getContextValue("Pdo:$name");
        if ($db == null) {
            /** @var PdoPools $pdoPools */
            $pdoPools = getDeepContextValueByClassName(PdoPools::class);
            $pool = $pdoPools->getPool($name);
            if ($pool == null) {
                throw new PostgresqlException("No Pdo connection pool named {$name} was found");
            }
            return $pool->db();
        } else {
            return $db;
        }
    }

    /**
     * Returns the internationalization (i18n) component
     * @return \ESD\Yii\I18n\I18N the internationalization application component.
     */
    public function getI18n()
    {
        $i18n = Yii::createObject([
            'class' => \ESD\Yii\I18n\I18N::class
        ]);

        return $i18n;
    }
}