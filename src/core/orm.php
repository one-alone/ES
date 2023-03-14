<?php

class Orm
{
    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = 'created';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = 'updated';
    public  function __construct () {

        global $GLOBALS;
        $db_configs = $GLOBALS['db']['connections'];
        $capsule = new \Illuminate\Database\Capsule\Manager;
        foreach ($db_configs as $name => $db_config){
            // 创建链接
            $capsule->addConnection($db_config,$name);
        }
        // 设置全局静态可访问DB
        $capsule->setAsGlobal();
//        // 数据库查询事件
//        $capsule->setEventDispatcher(new Dispatcher(new Container));
       // if ($is_readonly) {
            // 启动Eloquent （如果只使用查询构造器，这个可以注释）
            $capsule->bootEloquent();
       // }
        //parent::__construct();
        //$results = Capsule::connection('connect1')->select('select * from users where id = ?', [1]);
    }

}
new Orm();
