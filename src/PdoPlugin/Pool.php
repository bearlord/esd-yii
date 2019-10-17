<?php

namespace ESD\Yii\PdoPlugin;

use ESD\Core\Channel\Channel;
use ESD\Yii\Base\Application;
use ESD\Yii\Base\Yii;
use ESD\Yii\Db\Connection;


class Pool
{
    /**
     * @var Channel
     */
    protected $pool;
    /**
     * @var PostgresqlOneConfig
     */
    protected $config;

    /**
     * Pool constructor.
     * @param Config $config
     * @throws \ESD\Yii\Db\Exception
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $_config = $config->buildConfig();

        $this->pool = DIGet(Channel::class, [10]);

        for ($i = 0; $i < $config->getPoolMaxNumber(); $i++) {
            $db = $this->connect($config);
            $this->pool->push($db);
        }
    }

    /**
     * Connect
     * @param Config $config
     * @return Connection
     * @throws \ESD\Yii\Db\Exception
     */
    protected function connect($config)
    {
        $db = new Connection();
        $db->dsn = $config->getDsn();
        $db->username = $config->getUsername();
        $db->password = $config->getPassword();
        $db->tablePrefix = $config->getTablePrefix();
        $db->open();
        return $db;
    }

    /**
     * @return \ESD\Plugins\Postgresql\PostgresDb;
     * @throws \ESD\BaseServer\Exception
     */
    public function db()
    {
        $db = getContextValue("Pdo:{$this->getconfig()->getName()}");
        if ($db == null) {
            $db = $this->pool->pop();
            defer(function () use ($db) {
                $this->pool->push($db);
            });
            setContextValue("Pdo:{$this->getconfig()->getName()}", $db);
        }
        return $db;
    }

    /**
     * @return PostgresqlOneConfig
     */
    public function getconfig()
    {
        return $this->config;
    }

    /**
     * @param PostgresqlOneConfig $config
     */
    public function setconfig($config)
    {
        $this->config = $config;
    }
}