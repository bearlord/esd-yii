<?php

namespace ESD\Yii\PdoPlugin;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Core\Context\Context;
use ESD\Core\PlugIn\PluginInterfaceManager;

class PdoPlugin extends \ESD\Core\PlugIn\AbstractPlugin
{
    use GetLogger;

    protected $configs;

    public function __construct()
    {
        parent::__construct();
        $this->configs = new Configs();
    }

    public function getName(): string
    {
        return 'esd-yii-pdo';
    }

    public function init(Context $context)
    {
        return parent::init($context);
    }

    /**
     * Before server start
     *
     * @param Context $context
     * @return mixed|void
     * @throws \ReflectionException
     */
    public function beforeServerStart(Context $context)
    {
        $configs = Server::$instance->getConfigContext()->get('esd-yii.db.default');
        foreach ($configs as $key => $value) {
            $config = new Config();
            $config->setName($key);
            $this->configs->addConfig($config->buildFromArray($value));
        }
    }

    /**
     * Before process start
     *
     * @param Context $context
     * @return mixed|void
     * @throws \ReflectionException
     */
    public function beforeProcessStart(Context $context)
    {
        $pools = new Pools();

        $configs = $this->configs->getConfigs();
        if (empty($configs)) {
            $this->warn("没有PDO配置");
            return false;
        }

        foreach ($configs as $key => $config) {
            $pool = new Pool($config);
            $pools->addPool($pool);
            $this->debug(sprintf("已添加名为 %s 的 %s 连接池", $config->getName(), $config->getDriverName()));
        }

        $this->ready();
    }

    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
    }
}