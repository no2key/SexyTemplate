SexyTemplate
============

一个语法类似 [artTemplate](https://github.com/aui/artTemplate) 的 PHP 模板引擎。他的最大特点小巧精悍，不到五百行的代码包含了一下诸多功能。

> PS: SexyTemplate 需要 PHP VERSION >= 5.5.0

准确的错误捕捉
-------

错在哪里不知道，没关系有了这个一抓一个准。 [线上体验地址](http://tools.sou.la/SexyTemplate/demo/)

![error](https://raw.githubusercontent.com/qpwoeiru96/SexyTemplate/master/screenshot/error.png)

高效的模板语法糖
--------
各种逻辑控制以及转义都有高效简洁的替代写法

![syntax](https://raw.githubusercontent.com/qpwoeiru96/SexyTemplate/master/screenshot/syntax.png)

现在支持4种语法糖(默认不开启，请使用 enableXXXSyntax 系列函数开启, 可使用 enableAllSyntax 全部开启)：

```
enablePrintSyntax 用来支持 == "42" 跟 = $var 输出/转义输出
enableConditionPrintSyntax 支持 ?= $var1 : $var2和 ?== $var : "值不存在"语法 相比 == 跟 = 多了存在跟空值的判断
enableLoopSyntax  支持 loop $items $index $item 和 /loop 语法
enableIfSyntax 支持 if else elseif 和 /if 语法
```

简洁的注入方式
-------

无需各种各样的语法知识，只需一点点正则知识，你也可以给模板添加各种模板语法糖。

```
/**
 * 用来支持 == 跟 = 输出/转义输出
 */
$this->insertParser('#^(?<mark>={1,2}) *(?<statement>.*)#i', function ($mark, $statement) {
    return T_COLLECT_HEAD . ($mark === '==' ? $statement : "htmlspecialchars({$statement})");
});
```


简单的使用方式
-------
```
//实例化一个编译类
$ST = new SexyTemplate\Compiler();
$template = '<pre>hello world! this is <%var_dump($self)%>. <%==$content%></pre>';

//开启全部的语法糖
$st->enableAllSyntax();

//开启debug功能会提示语法错误
$st->debug = true;

//编译完成之后 会返回一个执行包装器
$wrapper = $ST->compile($template);

//你可以打印查看编译出来的源
//highlight_string('<?php ' . $wrapper);

//绑定 $self 环境 并且输入变量执行
print $wrapper
    ->bind($this)
    ->render(['content' => 'i am SexyTemplate']);
```

支持文件包含以及编译html文件
----------------
```
<%include "common/header"%>
<%include "common/footer"%>
```

```
$st = new SexyTemplate\FileCompiler();
$st->enableAllSyntax();
$st->templateDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates';
$st->debug = true;

$wrapper = $st->compileFile('index');

print $wrapper
    ->bind($this)
    ->render();
```

其他
--

等待客官的挖掘，也可查看我的文章 [《谈谈SexyTemplate》](http://blog.sou.la/2014/11/10/sexy_template/) 。