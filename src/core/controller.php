<?php


class Controller
{
    public $layout;
    private $_v;
    private $_data = array();
    public $routes;
    public $template_dir;
    public $_all_data = [0];
    public $_all_data_list = [];

    public function init()
    {
    }

    public function __construct()
    {
        global $__module, $__controller, $__action;
        $this->routes = ['m' => $__module, 'c' => $__controller, 'a' => $__action];
        $this->template_dir = APP_DIR . DS . "src" . DS . 'view' . DS . $this->routes['m'];
        $this->init();
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    public function listDisplay($fun = null)
    {
        if(!Common::is_ajax()){
            if($fun){
                $fun();
            }
            $this->display();
            exit();
        }
    }
    public function display($tpl_name = "", $return = false,$data=[],$count = 0,$totalRow = [])
    {

        if(Common::is_ajax() and !$return){
            Helper::responseJson('请求成功',0,$data,$count,$totalRow);
        }
        if (empty($tpl_name)) {
            $tpl_name = $this->routes['c'] . DS . $this->routes['a'] . ".php";
        }
        if (!$this->_v) {
            $this->_v = new View();
        }
        //controller 成员对模板外公开
        $this->_v->assign(get_object_vars($this));
        $this->_v->assign($this->_data);
        if ($this->layout) {
            $this->_v->assign('__render_body', $this->template_dir . DS . $tpl_name);
            $tpl_name = $this->layout;
        }
        if ($return) {
            //此方式保留方便action里面直接生成静态文件
            return $this->_v->render($this->template_dir . DS . $tpl_name);
        } else {
            echo $this->_v->render($this->template_dir . DS . $tpl_name);
        }
    }

    /**
     * 数据库模型验证
     * @param $data
     * @param $rules
     */
    public function validator($data,$rules){
        $v = App::validator($data);
        $v->mapFieldsRules($rules);
        if (!$v->validate()) {
            $errors = (array)$v->errors();
            $result = [];
            array_map(function ($value) use (&$result) {
                $result = array_merge($result, array_values($value));
            }, $errors);
            Helper::responseJson($result[0], 308);
        }
    }

    /**
     * 递归分页查询某个表某一列所有数据
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function allData($table,$where,$filed,$page = 1,$limit = 1000){
        $db =  new $table();
        $data = $db->findAll($where, 'id desc',$filed,[$page,$limit]);
        if(!$data){
            $allData = $this->_all_data;
            $this->_all_data = [0];
            return $allData;
        }
        $this->_all_data += array_column($data,$filed)?:[0];
        $page++;
        return $this->allData($table,$where,$filed,$page);
    }

    /**
     * 递归分页查询某个表所有数据
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function allDataList($table,$where,$filed='*',$page = 1,$limit = 1000){
        $db =  new $table();
        $data = $db->findAll($where, 'id desc',$filed,[$page,$limit]);
        if(!$data){
            $allData = $this->_all_data_list;
            $this->_all_data_list = [];
            return $allData;
        }
        foreach($data as $r){
            array_push($this->_all_data_list,$r);
        }
        $page++;
        return $this->allDataList($table,$where,$filed,$page);
    }



    /**
     * 写日志 为了兼容老版本写法 方法名加上s
     * */
    protected function writeLogs ($user_id, $data = [], $classify = 0) {
        SysLog::writeLog($user_id,
                         "{$this->routes['c']}/{$this->routes['a']}",
                         "{$this->routes['m']}",
                         $data,
                         $classify == 0 ? '请求' : '返回'
        );
    }
}