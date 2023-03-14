<form class="layui-form" action="{{?=Helper::url('<?=$table ?>', "form")?}}">

{{?php  if( $item['id'] and  $item['updated'] ) { ?}}
<blockquote class="layui-elem-quote"><strong>创建时间</strong>：{{?= $item['created']  ?}}
    <strong>上次修改时间</strong>： {{?= $item['updated']  ?}} </blockquote>
{{?php } ?}}

<?php foreach ($columns as $index => $column) {
    if($column['Field'] == 'created' or $column['Field'] == 'updated'){
        continue;
    }
    ?>
    <?php if (strtolower($column['Field']) == "id") { ?>
        <input type="hidden" id="id" name="id" value="{{?= $item['id'] ?}}">
    <?php } else { ?>
        <div class="layui-form-item">
            <label class="layui-form-label"><?=$column['comment']?$column['comment']: $column['Field'] ?></label>
            <div class="layui-input-inline">
                <input type="text" class="layui-input" id="<?= $column['Field'] ?>" name="<?= $column['Field'] ?>" lay-verify="<?= $column['verify'] ?>"
                       value="{{?= $item['<?= $column['Field'] ?>'] ?}}" autocomplete="off"
                       placeholder="请输入<?= $column['comment']?$column['comment']: $column['Field'] ?>">
            </div>
        </div>
    <?php } ?>
<?php } ?>
<div class="layui-form-item layui-hide">
    <div class="layui-input-block">
        <button class="layui-btn" lay-submit="" id="btnSubmit" lay-filter="btnSubmit">立即提交</button>
    </div>
</div>
</form>

<script src="{{?= APP_ROOT . $GLOBALS['static'] ?}}/public/js/form.js" charset="utf-8"></script>