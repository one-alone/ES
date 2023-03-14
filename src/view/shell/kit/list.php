<form class="layui-form layui-form-pane " lay-filter="searchForm">
    <div class="layui-form-item layui-inline">
        <div class="layui-form-item layui-inline">
            <label class="layui-form-label">关键字</label>
            <div class="layui-input-inline">
                <input type="text" id="keyword" name="keyword" value="{{?= Helper::request('keyword', '') ?}}"
                       placeholder="请输入关键字"
                       class="layui-input layui-input-small search_input">
            </div>
        </div>
    </div>
    <div class="layui-form-item layui-inline">
        <button class="layui-btn" id="search" data-type="reload"><i class="layui-icon">&#xe615;</i></button>
    </div>
    <div class="layui-form-item layui-inline">
        <a class="layui-btn addbtn" yxbd_method="form" yxbd_param="height:80%;width:40%;title:添加"
           authorize="yes" action="{{?= Helper::url('<?= strtolower($table) ?>', 'form') ?}}">添加</a>
    </div>
</form>
<div class="test-table-reload-btn">
    <div style="display: none;">
        <table class="layui-hide" id="export" lay-filter="table_export"></table>
    </div>
    <table class="layui-hide" id="demo" lay-filter="test"></table>
</div>
<script type="text/html" id="toolbarDemo">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="getCheckData">批量删除</button>
        <!--        <button class="layui-btn layui-btn-sm" lay-event="getCheckLength">获取选中数目</button>-->
        <!--        <button class="layui-btn layui-btn-sm" lay-event="isAll">验证是否全选</button>-->
    </div>
</script>
<script type="text/html" id="barDemo" style="width: 100%">
    <a class="layui-btn layui-btn-xs table-form" yxbd_method="form" yxbd_param="height:80%;width:40%;title:编辑"
       authorize="yes" action="{{?= Helper::url('<?= strtolower($table) ?>', 'form') ?}}" lay-event="form">
        <i class="layui-icon">&#xe642;</i>编辑
    </a>
    <a class="layui-btn layui-btn-xs layui-btn-danger" yxbd_method="confirm" authorize="yes"
       action="{{?= Helper::url('<?= strtolower($table) ?>', 'delete') ?}}" lay-event="delete">
        <i class="layui-icon">&#xe640;</i>删除
    </a>
    <!--    {{#  if(d.state == 0){ }}-->
    <!--    <a class="layui-btn layui-btn-xs " style="background-color: #5FB878" yxbd_method="confirm"-->
    <!--       yxbd_field="state:--><?//=Order::PAYMENTED?><!--" authorize="yes"-->
    <!--       action="--><?//= Helper::url('order', 'form') ?><!--" lay-event="delete">-->
    <!--        <i class="layui-icon">&#xe65e;</i>确认到账-->
    <!--    </a>-->
    <!--    <a class="layui-btn layui-btn-xs layui-btn-danger" yxbd_method="confirm" yxbd_field="state:--><?//=Order::REFUND?><!--" authorize="yes"-->
    <!--       action="--><?//= Helper::url('order', 'form') ?><!--" lay-event="delete">-->
    <!--        <i class="layui-icon">&#xe640;</i>取消-->
    <!--    </a>-->
    <!--    {{#  } }}-->
    <!--    {{#  if(d.state == 1){ }}-->
    <!--    <a class="layui-btn layui-btn-xs layui-btn-blue " yxbd_method="confirm" yxbd_field="state:--><?//=Order::CALLBACK?><!--" authorize="yes"-->
    <!--       action="--><?//= Helper::url('order', 'form') ?><!--" lay-event="delete">-->
    <!--        <i class="layui-icon">&#xe609;</i>封装完毕-->
    <!--    </a>-->
    <!--    {{#  } }}-->
</script>

<!--<script type="text/html" id="img">-->
<!--    <div class="layer-photos-demo">-->
<!--        <img class="img" width="50px" layer-src="{{d.img}}"-->
<!--             src="{{d.img}}" alt="">-->
<!--    </div>-->
<!--</script>-->
<script type="text/html" id="switchTpl">
    <!-- 这里的 checked 的状态只是演示 -->
    <input type="checkbox" name="state" value="{{d.id}}" lay-skin="switch"
           lay-text="开启|关闭" lay-filter="state" {{ d.state== 1 ? 'checked' : '' }}>
</script>
<script src="{{?= APP_ROOT . $GLOBALS['static'] ?}}/public/js/list.js" charset="utf-8"></script>
<script>
    layui.config({
        base: "/res/public/js/"
    }).use(['layer', 'jquery', 'table', 'form','laydate','common'], function () { //加载特定模块：layui.use(['layer', 'laydate'], function(){
        //得到各种内置组件
        var layer = layui.layer //弹层
            , $ = layui.jquery
            , table = layui.table //表格
            ,common = layui.common()
            , form = layui.form
            ,laydate = layui.laydate
            ,page = 1
        ;
        //日期时间范围
        laydate.render({
            elem: '#test10'
            , type: 'datetime'
            , range: true

        });
        //监听性别操作
        form.on('switch(state)', function (obj) {
            var id = this.value;
            var index = layer.load();
            $.post("{{?= Helper::url('main', 'state') ?}}", {id: id, model: "<?= $table ?>"}, function (res) {
                layer.close(index);
                layer.tips(res.msg, obj.othis);
                if (res.code > 0) {
                    setTimeout(function () {
                        tableReload();
                    }, 1500);
                }
            });

        });
        //执行一个 table 实例
        var tableIn = table.render({
            elem: '#demo'
            // ,height: 250
            , url: "{{?= Helper::url('<?= strtolower($table) ?>', 'list') ?}}"  //数据接口
            , title: '数据列表'
            , limit: "{{?= $_config['admin']['page'] ?}}"
            , page: true  //开启分页
            , cellMinWidth: 150
            ,toolbar: '#toolbarDemo' //开启头部工具栏，并为其绑定左侧模板
            , cols: [[ //表头
                //  {type: 'numbers', fixed: 'left'}
                {type:'checkbox', fixed: 'left'}
                ,{field: 'id', title: 'ID', fixed: 'left',minWidth: 60}
                // , {field: "img", title: "图片", templet: '#img'}
                <?php foreach ($columns as $index => $column) {
                if($column['Field'] == 'id' || $column['Field'] == 'state'){
                    continue;
                }
                ?>
                , {field: "<?=  $column['Field'] ?>", title: "<?= $column['comment'] ?>",}
                <?php } ?>

                , {field: 'state', title: '状态', templet: '#switchTpl'
                    // ,templet: function(d){
                    //     console.log(d.LAY_INDEX); //得到序号。一般不常用
                    //     console.log(d.LAY_COL); //得到当前列表头配置信息（layui 2.6.8 新增）。一般不常用
                    //
                    //     //得到当前行数据，并拼接成自定义模板
                    //     return 'ID：'+ d.id +'，标题：<span style="color: #c00;">'+ d.title +'</span>'
                    // }
                }
                , {fixed: 'right', title: '操作', align: 'center', toolbar: '#barDemo',width: 180}
            ]]
            ,done: function(res, curr, count){
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
                // console.log(res);
                // //得到当前页码
                // console.log(curr);
                page = curr
                // //得到数据总量
                // console.log(count);
                layer.photos({
                    photos: '.layer-photos-demo'
                });
                common.checkAction($('.layui-table-view'));
                $("div[lay-event=LAYTABLE_EXPORT]").click(function (){
                    tableExport(count)
                    return false;
                });
            }
            //   ,even: true //开启隔行背景
        });

        function tableReload(page) {
            var data = form.val("searchForm");
            $('#search').removeAttr('nowPage');
            tableIn.reload({
                page: {
                    curr: page //重新从第 1 页开始
                },
                where: data
            });
        }

        //搜索
        $('#search').on('click', function () {
            if($('#search').attr('nowPage') != 1){
                page = 1;
            }
            tableReload(page);
            return false;
        });
        //头工具栏事件
        table.on('toolbar(test)', function(obj){
            var checkStatus = table.checkStatus(obj.config.id);
            switch(obj.event){
                case 'getCheckData':
                    var data = checkStatus.data;
                    if(data.length === 0){
                        layer.msg('未选择数据');
                        return false;
                    }
                    var ids = [];
                    $.each(data,function (index,item){
                        ids.push(item.id);
                    })
                    layer.confirm("注：您确定要处理"+data.length +"条数据吗？", {icon: 3, title: '提示信息'}, function (index) {
                        var confirm_index = layer.load();
                        $.post("{{?= Helper::url('<?= strtolower($table) ?>', 'delete') ?}}", {ids:ids}, function (result) {
                            layer.close(confirm_index);
                            if (result.code == 0) {
                                layer.msg(result.message);
                                setTimeout(function () {
                                    tableReload();
                                }, 1000);
                            } else {
                                layer.msg(result.message);
                            }

                        }, "json");
                    });

                    //  layer.alert(JSON.stringify(data));
                    break;
                case 'getCheckLength':
                    var data = checkStatus.data;
                    layer.msg('选中了：'+ data.length + ' 个');
                    break;
                case 'isAll':
                    layer.msg(checkStatus.isAll ? '全选': '未全选');
                    break;

                //自定义头工具栏右侧图标 - 提示
                case 'LAYTABLE_TIPS':
                    layer.alert('这是工具栏右侧自定义的一个图标按钮');
                    break;
            };
        });
        //监听操作行工具事件
        table.on('tool(test)', function (obj) { //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
            var $ele = $(this);
            var data = obj.data //获得当前行数据
                , layEvent = obj.event; //获得 lay-event 对应的值
            switch(layEvent){
                case 'detail':
                    modelfull(data, $ele);
                    break;
                case 'form':
                    modalOpen(data, $ele);
                    break;
                case 'delete':
                    deleteForm(data, $ele);
                    break;
            }
        });
        function tableExport(table_count){
            var tableExc = table.render({
                elem: '#export'
                // ,height: 250
                , url: "{{?= Helper::url('<?= strtolower($table) ?>', 'list') ?}}"  //数据接口
                , title: '数据导出列表'
                , limit: table_count
                , where:form.val("searchForm")
                , page: true //开启分页
                , toolbar: true  //开启工具栏，此处显示默认图标，可以自定义模板，详见文档
                , cols: [[ //表头
                    //  {type: 'numbers', fixed: 'left'}
                    {field: 'id', title: 'ID',}
                    <?php foreach ($columns as $index => $column) {
                    if($column['Field'] == 'id' || $column['Field'] == 'state'){
                        continue;
                    }
                    ?>
                    , {field: "<?=  $column['Field'] ?>", title: "<?= $column['comment'] ?>",}
                    <?php } ?>
                ]]
                ,done: function(res, curr, count){
                    table.exportFile(tableExc.config.id, res.data); //data 为该实例中的任意数量的数据
                }
                //   ,even: true //开启隔行背景
            });
        }
    });
</script>
