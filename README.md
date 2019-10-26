# esd-yii
esd-yii是ESD【EasySwooleDistributed】的一个扩展、包含了pdo的plugin。

魔改了Yii2(v2.0.17)、已最小的改动兼容了ESD的用法。



## 魔改内容：

包含：Connection、Query、Model、ActiveRecord、Validator、I18n、 helpers、 Di、Component、Logger、Sectuirty、Cache、Redis、User、Identity、behaviors、events。

未包含内容：Request、Response、Controller、Router、View、data、Cookie、Session、Asset、Console、ErrorHandle、migrations等。



## 说明：

### 1.

Yii2是BSD协议。作者保留了原版协议、原作者、原注释。命名空间做了修改、部分代码注释与实际用法不一样、忽略注释中的命名空间即可、IDE可正常提示。



### 2.

初衷是想搬过来Query、Model、ActiveRecord、Validator、I18n。Component、Di必须包含。底层代码中有大量的Di、Logger、Cache部分、避免大幅改动、一并搬过来。behaviors、events也未做删除。



### 3.

Cache部分，去除了XCache、ApcCache、MemCache。

PHP7中未找到XCache的方法。

ESD中Redis是协程操作，如果用Memcache一样需要连接池与协程，不如直接用Redis的。

Redis Cache采用 yiisoft/yii2-redis 的扩展。链接部分改为ESD的Redis的连接池。



### 4.

Yii2的Request、Response、Cookie、Session，均替换成了ESD的。

CSRF TOKEN部分，迁移过来，未完全测试通过。[v0.1]，前后端分离的项目，暂时不用担心。



## 原理：

启动ESD时，启动【ESD\Yii\PdoPlugin\PdoPlugin】插件，启动PDO连接池，同时运行YII2的Application单例



用法：

```
composer require bearlord/esd-yii
```



## 用法：

### 1配置

文件：resources/application-local.yml

配置redis、session，然后配置esd-yii部分。

参考Yii2的配置，按照YML的格式，进行填写即可。

Yii2的数据库链接，主从，主主连接也照搬过来了，同时每个主、从单独实现了连接池，数量通过参数poolMaxNumber设定。

【按要求，ESD的YML配置，key值不能用驼峰，需要转换为下划线。我为了尽量与Yii的一致，没有转。】

```
reload:
  enable: true

mysql:
  default:
    host: 'localhost'
    username: 'root'
    password: '123456'
    db: 'sd_test'
    prefix: "p_"

redis:
  default:
    host: 'localhost'

session:
  timeout: 1800
  db: default
  session_storage_class: 'ESD\Plugins\Session\RedisSessionStorage'
  session_name: "session"
  session_usage: 'cookie'
  domain: ''
  path: '/'
  http_only: true
  secure: false

esd-yii:
  components:
    log:
      traceLevel: 0
      targets:
        -
          class: 'ESD\Yii\Log\FileTarget'
          levels:
          - warning
          - info
          - trace

    cache:
      class: 'ESD\Yii\Redis\Cache'

    admin:
      class: 'ESD\Yii\Web\User'
      identityClass: 'app\Model\MySQL\Admin'
      enableAutoLogin: true
      enableSession: true
      identityCookie:
        name: _identity-admin
        httpOnly: true

  language: 'zh-CN'
  db:
    default:
#      dsn: 'pgsql:host=192.168.108.130;dbname=sd_test'
#      username: 'postgres'
#      password: '123456'
#      tablePrefix: 'p_'
#      poolMaxNumber: 6
      dsn: 'mysql:host=192.168.108.130;dbname=sd_test'
      username: 'root'
      password: '123456'
      charset: 'utf8'
      tablePrefix: 'p_'
      poolMaxNumber: 2
      slaveConfig:
        username: 'root'
        password: '123456'
        poolMaxNumber: 2
      slaves:
        - dsn: 'mysql:host=192.168.108.130;dbname=sd_test'
        - dsn: 'mysql:host=192.168.108.130;dbname=sd_test'


```



### 2. Query

语法与Yii2一样。

```php
/**
 * @GetMapping("/query")
 * @return string
 */
public function query()
{
	$this->response->withHeader("Content-Type", "application/json;charset=UTF-8");

	$result =  (new Query())
		->from('p_photo_model')
		->where([
			'id' => 1713
		])
		->all();
	return [
		'result' => $result
	];
}
```



### 3.Model 与 Validator

文件：app\Model\YiiCustomer.php

```php
namespace app\Model;


use ESD\Yii\Db\ActiveRecord;

class YiiCustomer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'p_customer';
    }

    public function attributeLabels()
    {
        return [
            'user_name' => '用户名',
            'contact' => '联系方式'
        ];
    }

    public function rules()
    {
        return [
            [['user_name', 'contact'], 'required'],
            [['user_name', 'contact'], 'string', 'min' => 3, 'max' => 6]
        ];
    }

    public function scenarios()
    {
        return [
            'test' => ['user_name', 'contact']
        ];
    }
}
```



Controller部分

```php
use app\Model\YiiCustomer;

/**
 * @GetMapping("/validate")
 * @return string
 */
public function validate()
{
	$this->response->withHeader("Content-Type", "application/json;charset=UTF-8");

	$customer = new YiiCustomer();
	$customer->setScenario('test');
	$customer->setAttributes([
		'user_name' => 'abfffffffff'
	], false);
	$customer->validate();

	return $customer->errors;
}
```



### 4. 多语言

通过配置文件 resources/application-local.yml，esd-yii2的language可实现语言，用法、提示语与Yii2一样，未做修改。







