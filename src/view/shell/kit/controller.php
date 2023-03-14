{{?php

/**
 * 自动生成控制器
 * User: mr_zhou
 * Date: <?=date('Y-m-d H:i:s').PHP_EOL?>
 */
class <?=ucfirst($tableName)?>Controller extends BaseController
{

    /**
    * @var 操作模型
    */
    protected $model;

    public function init () {
        parent::init();
        $this->model = new <?=ucfirst($tableName)?>();
    }

    /**
    * @ApiDescription(section="doc示例", description="doc示例")
    * @ApiMethod(type="get")
    * @ApiRoute(name="/api/user/sons")
    * @ApiHeaders(name="Token", type="string", nullable=false, description="登录接口获取到的token")
    * @ApiParams(name="page", type="int", nullable=true, description="当前页码",sample="默认:1")
    * @ApiParams(name="limit", type="int", nullable=true, description="每页条数",sample="默认:10 后台设置")
    * @ApiReturn(type="object",description="{'code':0,'message':'数据集合'}", sample="{'code':0,
    * 'msg':'获取成功','data':{'data':[{'id':'用户ID','username':'账号','nickname':'昵称'}],'page':{'total_count':'数据总条数',
    * 'page_size':'每页数据条数', 'first_page':'首页页码','prev_page':'上一页页码','next_page':'下一页页码',
    *     'last_page':'最后一页页码','current_page':'当前页页码','all_pages':['所有页码数组']
    *     ,'offset':'偏移起始量','limit':'偏移量'}}}")
    */
    public function getList()
    {
        $this->listDisplay();
        $request = Helper::requestAll();
        [$list,$pageArr] = $this->model->all($request);
        $this->display('',false,$list,$pageArr['total_count']);
    }

    /**
    * 获取表单
    */
    public function getForm()
    {
        $id = Helper::request('id', '-1');
        $<?=$tableName?> = [];
        if ($id != '-1') {
            $where['id'] = $id;
            $<?=$tableName?> = $this->model->find($where, 'id desc', '*');
        }
        $this->item =  $<?=$tableName?>;
        $this->display();
    }

    /**
    * 设置表提交
    */
    public function postForm()
    {
        $data = Helper::requestAll();
        $this->validator($data,<?=ucfirst($tableName)?>::$rules);
        Model::transaction(function () use ($data){
            $id = Helper::request('id', 0);
            unset($data['id']);
            if ($id && $id > 0) {
                $re = $this->model->update(['id' => $id], $data);
                if(!$re){
                    throw new Exception('系统繁忙',1);
                }
            } else {
                $newId = $this->model->create($data);
                if ($newId == 0) {
                    throw new Exception('系统繁忙',2);
                }
            }
        });
        $this->responseJson('操作成功！');
    }

    /**
    * 删除
    */
    public function postDelete()
    {
        Model::transaction(function (){
            $<?=$tableName?>Db =  new <?=ucfirst($tableName)?>();
            $id = Helper::request('id', 0);
            $ids = Helper::request('ids', []);
            if (empty($id) and empty($ids)) {
                throw new Exception('请选择要删除的数据',1);
            }
            $re = false;
            if($id){
                $re = $this->model->delete(['id' => $id]);
            }elseif($ids){
                $re = $this->model->delete(["id in (:ids)",[':ids'=>$ids]]);
            }
            if(!$re){
                throw new Exception('系统繁忙',1);
            }
        });
        $this->responseJson('删除成功！');
    }
}