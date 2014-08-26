<?php
/* @Name:AlonePHP
   @Author:Tom<tom@awaysoft.com>
   @LastModify:2014-08-20
   @Description:AlonePHP是一个单文件框架，适用于制作简单的小工具，支持网页及命令模式
   @Copyright:本程序采用Apache 2.0授权协议
*/

/* 默认控制器 */
function IndexController() {
    template('Index');
}

/* 默认控制器模板 */
function IndexTemplate($args) {
    echo '欢迎使用AlonePHP框架';
}

/* 初始化函数，可以做权限验证，数据初始化等等 */
function init() {

}

/* 框架运行函数 */
function run() {    
    /* 初始化 */
    init();
    /* 获取控制器 */
    if (param_count() > 0) {
        /* 获取命令模式控制器 */
        $controller = param_get(1);
    } else {
        /* 获取网页模式 */
        $controller = get('c');
    }
    if (!$controller) {
        $controller = 'Index';
    }
    
    /* 移交控制权到相应的控制器 */
    controller($controller);
}

/* 模板输出接口函数
    @name: string, 模板名称
    @args: mixed, 传递到模板的参数，建议用关联数组
 */
function template($name, $args = '') {
    $templateName = $name . 'Template';
    if (function_exists($templateName)) {
        $templateName($args);
    } else {
        template('Error', "模板函数{$templateName}未找到！");
    }
}

/* 控制器接口函数
    @name: string, 控制器名称
 */
function controller($name) {
    $controllerName = $name . 'Controller';
    if (function_exists($controllerName)) {
        $controllerName();
    } else {
        template('Error', "控制器函数{$controllerName}未找到！");
    }
}

/*  程序打开参数个数 */
function param_count() {
    global $argc;
    return $argc - 1;
}

/*  获取程序参数 
    @index: integer, 参数的位置
*/
function param_get($index) {
    global $argc, $argv;
    if ($index > $argc) {
        return '';
    } else {
        return $argv[$index];
    }
}

/*  GET方法
    @name: string, GET参数
    @filter: 过滤函数
    @default: 默认值
*/
function get($name, $filter = 'htmlspecialchars', $default = '') {
    $result = $_GET[$name];
    if (!isset($result)) {
        $result = $default;
    }
    return $filter($result);
}

/*  POST方法
    @name: string, GET参数
    @filter: 过滤函数
    @default: 默认值
*/
function post($name, $filter = 'htmlspecialchars', $default = '') {
    $result = $_POST[$name];
    if (!isset($result)) {
        $result = $default;
    }
    return $filter($result);
}

/* 默认输出错误信息函数 */
function ErrorTemplate($args) {
    echo $args;
}

/* 运行框架 */
run();

/* 下面为可选函数区，可根据实际需求删减 */

