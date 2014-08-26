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
    /* 获取命令模式下参数变量 */
    global $argc, $argv;
    
    /* 初始化 */
    init();
    /* 获取控制器 */
    if ($argc > 1) {
        /* 获取命令模式 */
        $controller = $argv[1];
    } else {
        /* 获取网页模式 */
        $controller = $_GET['c'];
    }
    if (!$controller) {
        $controller = 'Index';
    }
    
    /* 查找对应控制器函数 */
    $controller .= 'Controller';
    if (function_exists($controller)) {
        $controller();
    } else {
        template('Error', "控制器函数{$controller}未找到！");
    }
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

/* 默认输出错误信息函数 */
function ErrorTemplate($args) {
    echo $args;
}

/* 运行框架 */
run();