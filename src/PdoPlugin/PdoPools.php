<?php

namespace ESD\Yii\PdoPlugin;


class PdoPools
{
    protected $poolList = [];

    /**
     * Get pool
     *
     * @param $name
     * @return
     */
    public function getPool($name = "default")
    {
        return $this->poolList[$name] ?? null;
    }

    /**
     * 添加连接池
     * @param Pool $pool
     */
    public function addPool(PdoPool $pool)
    {
        $this->poolList[$pool->getConfig()->getName()] = $pool;
    }

    /**
     * @return PostgresDb
     * @throws PostgresqlException
     * @throws \ESD\BaseServer\Exception
     */
    public function db()
    {
        $default = $this->getPool();
        if ($default == null) {
            throw new Exception("没有设置默认的PDO");
        }
        return $this->getPool()->db();
    }
}