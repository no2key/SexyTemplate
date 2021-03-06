<?php
/**
 * SexyTemplate
 * a mini php template engine. just like artTemplate(JavaScript).
 *
 * @author qpwoeiru96 <qpwoeiru96@gmail.com>
 * @version 0.0.1
 */

class SexyTemplate
{
    const T_FUNCTION_HEAD = 'extract($vars);unset($vars);$STR = ""; $STC = function($s) use(&$STR) { $STR .= $s;};';
    const T_FUNCTION_END = 'unset($STC);return $STR;';
    const T_STATEMENT_END =  ";";
    const T_COLLECT_HEAD_A = '$STC(';
    const T_COLLECT_END_A = ');';
    const T_COLLECT_HEAD_B = '$STV = ';
    const T_COLLECT_END_B = ';$STC(htmlspecialchars($STV));unset($STV);';

    /**
     * 是否验证模板的可用性 [建议在DBEUG阶段开启]
     * @var bool
     */
    public $validate = false;

    /**
     * 是否打印出错误的地方 [建议在DBEUG阶段开启]
     * @var bool
     */
    public $printError = false;

    /**
     * 核心方法 用于分析语句
     *
     * @param  string $statement
     * @return string
     */
    protected function parseStatement($statement)
    {
        if($statement{0} == '=' && $statement{1} == '=') {
            return self::T_COLLECT_HEAD_A . substr($statement, 2) . self::T_COLLECT_END_A;
        } elseif ($statement{0} == '=') {
            return self::T_COLLECT_HEAD_B . substr($statement, 1) . self::T_COLLECT_END_B;
        } else {
            return $statement . self::T_STATEMENT_END;
        }
    }

    /**
     * 转义字符串
     *
     * @param  string $str 需要进行转义的字符串
     * @return string
     */
    protected function escapeNormalString($str)
    {
        return str_replace("'", "\\'", str_replace('\\', '\\\\', $str));
    }

    /**
     * 收集正常输出的文本
     * 
     * @param  string $string 要输出的文本
     * @return string
     */
    protected function collectString($string)
    {
        if($string === '') return '';
        return self::T_COLLECT_HEAD_A . "'" . $this->escapeNormalString($string) . "'" . self::T_COLLECT_END_A;
    }

    /**
     * 编译模板
     * 
     * @param  string $template 模板字符串
     * @return string 返回函数主体
     */
    private function _compile($template)
    {
        $length    = strlen($template);
        $output  = array();
        $inStatement = false;
        $statement   = '';

        $output[] = self::T_FUNCTION_HEAD;

        for($i = 0; $i < $length; $i++) {

            $lastChar = $i > 0 ? $template{$i-1} : '';
            $char     = $template{$i};
            $nextChar = $length > $i + 1 ? $template{$i+1} : '';

            switch ($char) {
                case '<':
                    if($nextChar == '%' && !$inStatement) {
                        $output[] = $this->collectString($statement);
                        $statement = '';
                        $inStatement = true;
                        $i++;                        
                    } else {
                        $statement .= $char;
                    }                    
                    break;         
                case '%':
                    if($nextChar == '>' && $inStatement) {
                        $output[] = $this->parseStatement($statement);
                        $statement = '';
                        $inStatement = false;
                        $i++;
                    } else {
                        $statement .= $char;
                    }
                    break;
                default:
                    $statement .= $char;
                    if($i == $length - 1) {
                        if($inStatement)
                            $output[] = $this->parseStatement($statement);
                        else
                            $output[] = $this->collectString($statement);
                    }
                    break;
            }
            
        }

        $output[] = self::T_FUNCTION_END;

        return implode(PHP_EOL, $output);
    }

    /**
     * 编译模板
     * 
     * @param  string $template 需要编译的模板
     * @param  bool $returnBody 是否返回函数主体
     * @return string|\Closure
     */
    public function compile($template, $returnBody = false)
    {
        $funcBody = $this->_compile($template);

        if($this->validate && !$this->validate($template, $funcBody))
            throw new \Exception('template syntax error');

        return $returnBody ? $funcBody : create_function('$vars', $funcBody);
    }

    /**
     * 验证模板或者编译完成的语句是否有语法错误
     *
     * @param string $template 需要验证的模板
     * @param string|null $body 编译完成的语句
     * @return bool
     */
    public function validate($template, $body = null)
    {
        if($body === null)
            $body = $this->_compile($template);

        $f = @create_function('$vars', $body);

        if($f) return true;
        elseif(!$this->printError) return false;

        $error = error_get_last();

        $this->_printError($error, $template, $body);

        return false;
    }

    /**
     * 友好输出错误地址
     *
     * @param array $error 语法错误信息
     * @param string $template 需要验证的模板
     * @param string $body 编译完成的语句
     */
    private function _printError($error, $template, $body)
    {
        $body = '<?php ' . $body;
        $template = htmlspecialchars($template);
        $body = htmlspecialchars($body);
        print <<<EOT
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SexyTemplate Error</title>
<style>/*! normalize.css v3.0.2 | MIT License | git.io/normalize */html{font-family:sans-serif;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%}body{margin:0}article,aside,details,figcaption,figure,footer,header,hgroup,main,menu,nav,section,summary{display:block}audio,canvas,progress,video{display:inline-block;vertical-align:baseline}audio:not([controls]){display:none;height:0}[hidden],template{display:none}a{background-color:transparent}a:active,a:hover{outline:0}abbr[title]{border-bottom:1px dotted}b,strong{font-weight:bold}dfn{font-style:italic}h1{font-size:2em;margin:.67em 0}mark{background:#ff0;color:#000}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sup{top:-0.5em}sub{bottom:-0.25em}img{border:0}svg:not(:root){overflow:hidden}figure{margin:1em 40px}hr{-moz-box-sizing:content-box;box-sizing:content-box;height:0}pre{overflow:auto}code,kbd,pre,samp{font-family:monospace,monospace;font-size:1em}button,input,optgroup,select,textarea{color:inherit;font:inherit;margin:0}button{overflow:visible}button,select{text-transform:none}button,html input[type="button"],input[type="reset"],input[type="submit"]{-webkit-appearance:button;cursor:pointer}button[disabled],html input[disabled]{cursor:default}button::-moz-focus-inner,input::-moz-focus-inner{border:0;padding:0}input{line-height:normal}input[type="checkbox"],input[type="radio"]{box-sizing:border-box;padding:0}input[type="number"]::-webkit-inner-spin-button,input[type="number"]::-webkit-outer-spin-button{height:auto}input[type="search"]{-webkit-appearance:textfield;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;box-sizing:content-box}input[type="search"]::-webkit-search-cancel-button,input[type="search"]::-webkit-search-decoration{-webkit-appearance:none}fieldset{border:1px solid #c0c0c0;margin:0 2px;padding:.35em .625em .75em}legend{border:0;padding:0}textarea{overflow:auto}optgroup{font-weight:bold}table{border-collapse:collapse;border-spacing:0}td,th{padding:0}
.prettyprint{background:black;font-family:Menlo,'Bitstream Vera Sans Mono','DejaVu Sans Mono',Monaco,Consolas,monospace;font-size:12px;line-height:1.5;border:1px solid #ccc;padding:10px}.pln{color:white}@media screen{.str{color:#6f0}.kwd{color:#f60}.com{color:#93c}.typ{color:#458}.lit{color:#458}.pun{color:white}.opn{color:white}.clo{color:white}.tag{color:white}.atn{color:#9c9}.atv{color:#6f0}.dec{color:white}.var{color:white}.fun{color:#fc0}}@media print,projection{.str{color:#060}.kwd{color:#006;font-weight:bold}.com{color:#600;font-style:italic}.typ{color:#404;font-weight:bold}.lit{color:#044}.pun,.opn,.clo{color:#440}.tag{color:#006;font-weight:bold}.atn{color:#404}.atv{color:#060}}ol.linenums{margin-top:0;margin-bottom:0;color:white}a{color:gray;text-decoration:none}.container{width:1120px;margin:0 auto}body{font-family:PingHei,'Lucida Grande','Lucida Sans Unicode',Helvetica,Arial,Verdana,sans-serif}
</style>
</head>
<body>
    <div class="container">
        <h1>SexyTemplate Error</h1>
        <p>错误信息: {$error['message']}<p>
        <p>错误位置: 第{$error['line']}行<p>
        <div class="code-area">
            <p>模板代码:</p>
            <div class="left-side">
                <pre class="code-block prettyprint linenums:1 lang-html">{$template}</pre>
            </div>
            <p>PHP代码:</p>
            <div class="right-side">
                <pre class="code-block prettyprint linenums:1 lang-php">{$body}</pre>
            </div>
        </div>
    </div>
    <div style="text-align: center; color: gray;">author: <a href="http://blog.sou.la/">qpwoeiru96</a></div>
    <script src="//cdnjs.cloudflare.com/ajax/libs/prettify/r224/prettify.js"></script>
    <script>
    window.onload = prettyPrint;
    </script>
</body>
</html>
EOT;
        exit(-1);
    }
}

$st = new SexyTemplate();
print $st->compile('
hello world, i am <%=$name%>
<%foreach ($favourites as $fav) {%>
i like <%=$fav%>.
<%}%>', true);


/******************* Example ***********************

$s = new SexyTemplate();
$s->validate = true;
$s->printError = true;
$t = <<<EOT
        <div class="page">
            <span class="page-info">
                <a class="prev<%if (!\$hasPrev){%> prev-disabled<%}%>">
                    <i>
                    </i>
                    上一页
                </a>
                <ul class="page-list">
                    <%for (\$i = \$start; \$i <= \$end; \$i ++) {%>
                    <li class="num min<%if (\$i == \$page) {%> cur<%}%>" data-page="<%==\$i%>">
                        <a href="?page=<%=\$i%>"><%==\$i%></a>
                    </li>
                    <%}%>
                    <li class="pagination-break">
                        ...
                    </li>
                    <li class="num" data-page="<%==\$pageCount%>">
                        <%==\$pageCount%>
                    </li>
                </ul>
                <a class="next<%if (!\$hasNext){%> next-disabled<%}%>">
                    下一页
                    <i>
                    </i>
                </a>
                <span class="page-sum">
                    共<%==\$pageCount%>页
                </span>
            </span>
            <span class="page-go">
                <i class="fl">
                    跳转
                </i>
                <input type="text fl">
                <i class="fl">
                    页
                </i>
            </span>
            <a class="page-btn">
                GO
            </a>
        </div>
EOT;
$f = $s->compile($t);
print $f(array(
    'page' => 1,
    'hasPrev' => false,
    'start' => 5,
    'end' => 14,
    'pageCount' => 34,
    'hasNext' => true,
));
***************************************************/