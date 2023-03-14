<?php
/**
 * Created by PhpStorm.
 * User: echosong
 * Date: 2017/9/11
 * Time: 11:05
 */

use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

class DocController extends BaseController
{
    /**
     * 生成帮助文档脚本 php index.php shell doc apidoc
     */
    public function actionApiDoc () {

        spl_autoload_register(function ($class) {
            $file = APP_DIR . '/src/controller/api/' . $class . '.php';
            if (file_exists($file)) {
                include $file;
            }
        });
        $__controller = $_REQUEST['p'] . "Controller";
        $output_dir = APP_DIR . '/apidocs';
        $output_file = $__controller . '.html';
        try {
            $builder = new Builder([$__controller], $output_dir, $__controller . 'Title', $output_file);
            $builder->generate();
            echo $__controller . ' success'.PHP_EOL;
        } catch (Exception $e) {
            echo 'There was an error generating the documentation: ', $e->getMessage().PHP_EOL;
        }
    }

    private function html ($name = '') {
        return <<<"EOT"
        <!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="php-apidoc - apid documenation generator">
  <meta name="author" content="Calin Rada">
  <title>文档列表</title>
  <link href="https://netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css" rel="stylesheet">
  <style type="text/css">
    body      { padding-top: 70px; margin-bottom: 15px; }
    .tab-pane { padding-top: 10px; }
    .mt0      { margin-top: 0px; }
    .footer   { font-size: 12px; color: #666; }
    .label    { display: inline-block; min-width: 65px; padding: 0.3em 0.6em 0.3em; }
    .string   { color: green; }
    .number   { color: darkorange; }
    .boolean  { color: blue; }
    .null     { color: magenta; }
    .key      { color: red; }
    .popover  { max-width: 400px; max-height: 400px; overflow-y: auto;}
  </style>
</head>
<body>


<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h5>前端api列表</h5>

    </div>
  </div>
  <hr>
  <div class="panel-group" id="accordion">
    <a href="./UserController.html"><h5  >用户相关</h5></a>
    <a href="./UserController.html"><h5  >购物车</h5></a>
    <a href="./UserController.html"><h5  >订单相关</h5></a>

  </div>
  <hr>
<!--  <div class="row">-->
<!--    <div class="col-md-12">-->
<!--      <h5>后台api列表</h5>-->

<!--    </div>-->
<!--  </div>-->
<!--  <hr>-->
<!--  <div class="panel-group" id="accordion1">-->
<!--    <a href="./admin/UserController.html"><h2  >用户相关</h2></a>-->
<!--    <a href="./admin/ActionController.html"><h2  >菜单权限相关</h2></a>-->
<!--  </div>-->

  <div class="row mt0 footer">
    <div class="col-md-6" align="left">
      Generated on 2021-04-30, 09:38:02
    </div>
    <div class="col-md-6" align="right">
      <a href="https://github.com/calinrada/php-apidoc" target="_blank">php-apidoc v1.3.8</a>
    </div>
  </div>

</div> <!-- /container -->


</body>
</html>
EOT;

    }

    /**
     * 站点发布脚本 php index.php shell doc release
     */
    public function actionRelease () {
        define("DEFAULT_PATH", "H:/release/yxbd");
        define("DEFAULT_APP", "web");

        fwrite(STDOUT, '请输入要发布的位置（默认为' . DEFAULT_PATH . '）：');
        $path = fgets(STDIN);
        if (strlen($path) < 3) {
            $path = DEFAULT_PATH;
        }
        fwrite(STDOUT, '请输入要发布的项目（默认为 ' . DEFAULT_APP . '）：');
        $m = trim(fgets(STDIN));
        if (strlen($m) < 3) {
            $m = DEFAULT_APP;
        }
        $fileSystem = new Symfony\Component\Filesystem\Filesystem();
        if ($fileSystem->exists([$path . '/' . $m . "/"])) {
            $fileSystem->remove([$path . '/' . $m . "/"]);
        }
        echo "clear \r\n";
        $fileSystem->mkdir($path);
        $fileSystem->copy(APP_DIR . "/index.php", $path . "/" . $m . "/index.php");
        $fileSystem->mirror(APP_DIR . "/vendor", $path . "/" . $m . "/vendor");
        $fileSystem->mirror(APP_DIR . "/src/controller/" . $m, $path . "/" . $m . "/src/controller/" . $m);
        $fileSystem->mirror(APP_DIR . "/src/view/" . $m, $path . "/" . $m . "/src/view/" . $m);
        $fileSystem->mirror(APP_DIR . "/src/core", $path . "/" . $m . "/src/core");
        $fileSystem->mirror(APP_DIR . "/src/plugin", $path . "/" . $m . "/src/plugin");
        $fileSystem->mirror(APP_DIR . "/src/model", $path . "/" . $m . "/src/model");
        $fileSystem->mirror(APP_DIR . "/res", $path . "/" . $m . "/res");
        echo "published";
    }
}