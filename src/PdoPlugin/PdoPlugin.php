<?php

namespace ESD\Yii\PdoPlugin;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Server;
use ESD\Core\Context\Context;
use ESD\Core\PlugIn\PluginInterfaceManager;
use ESD\Yii\Base\Application;

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
        $configs = Server::$instance->getConfigContext()->get("esd-yii.db");

        foreach ($configs as $key => $config) {
            $configObject = new Config();
            $configObject->setName($key);
            $this->configs->addConfig($configObject->buildFromConfig($config));

            $slaveConfigs = $this->getSlaveConfigs($config);
            if (!empty($slaveConfigs)) {
                foreach ($slaveConfigs as $slaveKey => $slaveConfig) {
                    $slaveConfigObject = new Config();
                    $slaveConfigObject->setName(sprintf("%s.slave.%s", $key, $slaveKey));
                    $this->configs->addConfig($slaveConfigObject->buildFromConfig($slaveConfig));
                }
            }
        }


        Application::instance();
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
        $pools = new PdoPools();

        $configs = $this->configs->getConfigs();
        if (empty($configs)) {
            $this->warn("没有PDO配置");
            return false;
        }

        foreach ($configs as $key => $config) {
            $pool = new PdoPool($config);
            $pools->addPool($pool);
            $this->debug(sprintf("已添加名为 %s 的 %s 连接池", $config->getName(), $config->getDriverName()));
        }

        $context->add("PdoPool", $pools);
        $this->setToDIContainer(PdoPools::class, $pools);
        $this->setToDIContainer(PdoPool::class, $pools->getPool());

        $this->ready();
    }

    /**
     * @param PluginInterfaceManager $pluginInterfaceManager
     * @return mixed|void
     */
    public function onAdded(PluginInterfaceManager $pluginInterfaceManager)
    {
        parent::onAdded($pluginInterfaceManager);
    }

    /**
     * @param $config
     * @return array|bool
     */
    protected function getMasterConfigs($config)
    {
        if (empty($config['masters'])) {
            return false;
        }
        if (empty($config['masterConfig'])) {
            return false;
        }
        $row = [];
        foreach ($config['masters'] as $k => $v) {
            $v['username'] = $config['masterConfig']['username'];
            $v['password'] = $config['masterConfig']['password'];
            $v['poolMaxNumber'] = $config['masterConfig']['poolMaxNumber'];
            $v['charset'] = $config['charset'];
            $v['tablePrefix'] = $config['tablePrefix'];
            $row[] = $v;
        }
        return $row;
    }

    /**
     * @param $config
     * @return array|bool
     */
    protected function getSlaveConfigs($config)
    {
        if (empty($config['slaves'])) {
            return false;
        }
        if (empty($config['slaveConfig'])) {
            return false;
        }
        $row = [];
        foreach ($config['slaves'] as $k => $v) {
            $v['username'] = $config['slaveConfig']['username'];
            $v['password'] = $config['slaveConfig']['password'];
            $v['poolMaxNumber'] = $config['slaveConfig']['poolMaxNumber'];
            $v['charset'] = $config['charset'];
            $v['tablePrefix'] = $config['tablePrefix'];
            $row [] = $v;
        }
        return $row;
    }
}