<?php
include __DIR__ . '/../src/SexyTemplate.php';

class Controller
{
    public $name = 'SexyTemplate';

    public function actionIndex()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $template = isset($_POST['template']) ? $_POST['template'] : '';
            return $this->compileTemplate($template);
        }

        $st = new SexyTemplate\FileCompiler();
        $st->enableAllSyntax();
        $st->templateDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates';
        $st->debug = true;

        $wrapper = $st->compileFile('index');

        #highlight_string('<?php ' . $wrapper);

        print $wrapper
            ->bind($this)
            ->render();
    }

    public function compileTemplate($template)
    {
        $st         = new SexyTemplate\Compiler();
        $st->enableAllSyntax();

//        $st->insertParser('#^pc:(?<func>[\w]+)(?<args>.*)#', function ($func, $args) {
//
//            preg_match_all('#([a-z]+)\=\"?([^\"]+)\"?#i', $args, $matches);
//            $var1 = $matches[1];
//            $var2 = $matches[2];
//
//            $expression = ['$args = array();'];
//
//            foreach($var1 as $k => $v) {
//                $expression[] = "\$args['{$v}'] = \"{$var2[$k]}\";";
//            }
//            return implode("\n", $expression) . ";\n" . '$data = pc_tag::' . $func . '($args);';
//
//        });

        $st->debug  = false;
        $expression = (string)$st->compile($template);
        $f          = @create_function('', $expression);
        $error = false;

        if(!$f) {
            $error = error_get_last();
            unset($error['type']);
            unset($error['file']);
        }

        print json_encode([
            'expression' => $expression,
            'error' => $error ? $error : null
        ]);
    }
}

$controller = new Controller();
$controller->actionIndex();
