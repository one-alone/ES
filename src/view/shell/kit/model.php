{{?php

/**
* 自动生成数据模型
* 名称：<?=$tableComment.PHP_EOL?>
* User: mr_zhou
* Date: <?=date('Y-m-d H:i:s').PHP_EOL?>
*/

class <?=ucfirst($table)?> extends Model
{
    //表名
    public $table_name = '<?=$tableName?>';

    //验证字段规则
    public static $rules = [
        <?=$rules?>
    ];

    //数据库字段
    /**
      *  public $fields = ['<?=$fields?>'];
    **/

    /**
    * 组装where条件语句
    * @param $data
    * @return string[]
    */
    private  function where($data){

        $where = $data['where'] ?? [' 1 = 1 '];
        if (isset($data['id']) && $data['id'] > 0) {
            $where[0] .= ' and (id = :id )';
            $where[1][':id'] =  $data['id'];
        }
        if (isset($data['keyword']) && !empty($data['keyword'])) {
            $where[0] .= ' and (name like :keyword )';
            $where[1][':keyword'] = '%' . $data['keyword'] . '%';
        }

        if (isset($data['state']) && $data['state'] != -1) {
            $where[0] .= ' and (state = :state )';
            $where[1][':state'] =  $data['state'];
        }
        return $where;
    }

    /**
    * 获取所有数据 最多1000 防止大查询
    * @param $data
    * @param $field
    * @param $sort
    * @return array
    */
    public  function getAll($data,$field='*',$sort='id desc')
    {
        $where = $this->where($data);
        $limit = $data['limit'] ?? 1000;
        $list = $this->findAll($where, $sort, $field, [$data['page'], $limit]);
        $pageArr = $this->page;
        return [$list,$pageArr];
    }

    /**
    * 获取单条数据
    * @param $data
    * @param $field
    * @param $sort
    * @return array
    */
    public  function getOne($data,$field='*',$sort='id desc')
    {
        $where = $this->where($data);
        return $this->find($where, $sort, $field);
    }
}