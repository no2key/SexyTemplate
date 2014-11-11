<?php
include __DIR__ . '/../src/SexyTemplate.php';

class Controller
{
    public $name = 'test';

    public function actionIndex()
    {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $template = isset($_POST['template']) ? $_POST['template'] : '';
            return $this->compileTemplate($template);
        }

        $st = new SexyTemplate\FileCompiler();
        $st->enableAllSyntax();
        $st->templateDir = __DIR__ . DIRECTORY_SEPARATOR . 'templates';
        $st->debug = false;

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
