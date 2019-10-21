<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 2019/10/17
 * Time: 10:47
 */

namespace ESD\Yii\PdoPlugin;


use ESD\Core\Plugins\Config\BaseConfig;

class Config extends BaseConfig
{
    const key = "pdo";
    /**
     * @var string
     */
    protected $name;
    /**
     * @var int
     */
    protected $poolMaxNumber;
    /**
     * @var string
     */
    protected $dsn;
    /**
     * @var string
     */
    protected $username;
    /**
     * @var string
     */
    protected $password;
    /**
     * 表前缀
     * @var string
     */
    protected $tablePrefix;
    /**
     * @var string
     */
    protected $charset;

    /**
     * Config constructor.
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param string $tablePrefix
     * @param string $charset
     * @param string $name
     * @param int $poolMaxNumber
     */
    public function __construct(string $dsn = '', string $username = '', string $password = '', string $tablePrefix = '', string $charset = "utf8", string $name = "default", $poolMaxNumber = 10)
    {
        parent::__construct(self::key, true, "name");
        $this->name = $name;
        $this->poolMaxNumber = $poolMaxNumber;
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->tablePrefix = $tablePrefix;
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPoolMaxNumber(): int
    {
        return $this->poolMaxNumber;
    }

    /**
     * @param int $poolMaxNumber
     */
    public function setPoolMaxNumber(int $poolMaxNumber): void
    {
        $this->poolMaxNumber = $poolMaxNumber;
    }

    /**
     * @return string
     */
    public function getDsn(): string
    {
        return $this->dsn;
    }

    /**
     * @param string $dsn
     */
    public function setDsn(string $dsn): void
    {
        $this->dsn = $dsn;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getDb(): string
    {
        return $this->db;
    }

    /**
     * @param string $db
     */
    public function setDb(string $db): void
    {
        $this->db = $db;
    }

    /**
     * @return string
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * @param string $tablePrefix
     */
    public function setTablePrefix(string $tablePrefix): void
    {
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * 构建配置
     * @throws postgresqlException
     */
    public function buildConfig()
    {
        return [
            'dsn' => $this->dsn,
            'username' => $this->username,
            'password' => $this->password,
            'tablePrefix' => $this->tablePrefix,
            'charset' => $this->charset,
            'poolMaxNumber' => $this->poolMaxNumber
        ];
    }

    /**
     * @param $array
     */
    public function buildFromArray($array)
    {
        $self = new self();
        $self->setDsn($array['dsn']);
        $self->setUsername($array['username']);
        $self->setPassword($array['password']);
        $self->setCharset($array['charset']);
        $self->setTablePrefix($array['tablePrefix']);
        $self->setPoolMaxNumber($array['poolMaxNumber']);
        return $self;
    }


    /**
     * Returns the name of the DB driver. Based on the the current [[dsn]], in case it was not set explicitly
     * by an end user.
     * @return string name of the DB driver
     */
    public function getDriverName()
    {
        if (($pos = strpos($this->dsn, ':')) !== false) {
            $driverName = strtolower(substr($this->dsn, 0, $pos));
        }

        return $driverName;
    }
}