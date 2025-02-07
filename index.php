<?php
$debug = 1;
// Пример использования функции:
define ('DATA_DIR',        "data");
define ('DS', DIRECTORY_SEPARATOR);
define ('FILE_EXTENSION', "tex");                //  расширения файлов ЛаТекс



$cat              = (array_key_exists('cat', $_REQUEST))?  $_REQUEST['cat']:"chatGPT";
$tex              = (array_key_exists('tex', $_REQUEST))?  $_REQUEST['tex']:"learn_LaTexP";
$show_row_numbers = (array_key_exists('show_row_numbers', $_REQUEST))? $_REQUEST['show_row_numbers']:false;
$tex_path         = DATA_DIR. DS . $cat . DS . $tex . "." . FILE_EXTENSION;
$dirs             = get_dirs();
extract($_GET);
//if($debug)print_mess( basename(__file__). " " . __line__ . " \$_REQUEST:" , $_REQUEST);
//if($debug)print_mess( basename(__file__). " " . __line__ . " \$cat "  , $cat);
//if($debug)print_mess( basename(__file__). " " . __line__ . " \$tex_path "  , $tex_path);

//$f_name   = array_key_exists('tex', $_REQUEST)? $_REQUEST['tex']:"learn_LaTexP" ; 
$directory = DATA_DIR.DS.$cat;//DATA_DIR.DS.$dirs[0].DS.$f_name;                   
$extension = '.'. FILE_EXTENSION;         // Замените на нужное окончание файла

$files = scanDirectoryForFiles($directory, $extension);
//if($debug)print_mess( basename(__file__). " " . __line__ . " \$files "  , $files);

// create menu
$menu  = create_menu($dirs);
echo $menu;



// Выводим результат
//if($debug)print_mess( basename(__file__). " " . __line__ . " Files with extension " . $extension , $files);
//if($debug)print_mess( basename(__file__). " " . __line__ . " \$menu: "  , $menu); 
//-------
// Текстовое содержимое задачи
$text = file_get_contents($tex_path);             //            20241125.txt") ;

$head ="";
$titles = get_title_subtitle();
// if ($debug) print_mess(__line__ . " \$text ", $text ); 
///          functions ////////////////////
function create_menu($dirs_){
$menu = "<nav class='navbar navbar-expand-lg navbar-light bg-light'>
          <div class='container-fluid'>
            <button class='navbar-toggler' type='button' data-bs-toggle='collapse' data-bs-target='#navbarNav' aria-controls='navbarNav' aria-expanded='false' aria-label='Toggle navigation'>
              <span class='navbar-toggler-icon'></span>
            </button>
            <div class='collapse navbar-collapse' id='navbarNav'>
              <ul class='navbar-nav'>";

foreach ($dirs_ as $key => $val) {
    // сканируем каждый директорий для получения списка файлов
    $menu .= "<li class='nav-item dropdown'>
                <a class='nav-link dropdown-toggle' href='?' id='navbarDropdown{$key}' role='button' data-bs-toggle='dropdown' aria-expanded='false'>
                  {$val}
                </a>
                <ul class='dropdown-menu' aria-labelledby='navbarDropdown{$key}'>";

    $_ = scandir(DATA_DIR . DS . $val); // сканируем параграф, категорию на поиск файлов
    $_ = clear_dots($_);

    foreach ($_ as $f => $tex_) {
        if (str_ends_with($tex_, FILE_EXTENSION)){        // only fo PHP 8.x    (substr($tex_, -1*(strlen(FILE_EXTENSION) )) == FILE_EXTENSION) {
            $tex = pathinfo($tex_, PATHINFO_FILENAME);
            $menu .= "<li><a class='dropdown-item' href='?cat={$dirs_[$key]}&tex={$tex}'>{$tex_}</a></li>";
        }
    }

    $menu .= "</ul></li>";
}

$menu .= "</ul></div></div></nav>";

return $menu;
}
function get_dirs(){
$dir_     = clear_dots(scandir(DATA_DIR));       // here must be only directories                       
$dirs = [];                               // the list of directories with latex formatted files 
foreach($dir_  as $key=>$val){            // получаем список директорй подлежащих сканированию на файлы
  if(@scandir(DATA_DIR. DS .$val)) $dirs[] = $val;         // получаем список директорй без пути, удобные для создания надписей меню
}
return $dirs;
}
function clear_dots($a=[]){
$ret=$a;
array_shift($ret);                       // remove the .
array_shift($ret);                       // remove the ..
return $ret;
}
// Заменим markdown заголовки и список на HTML получает строку и выдает строку  '/Me:(.*?)chatGPT:/s'
function markdown_to_html($text){     
//    $text = preg_replace('/### Me: (.*?)chatGPT:/s', "<p class='me'>\$1</p>", $text);
//    $text = preg_replace('/### chatGPT: (.*?)Me:/s', "<p class='chatGPT'>\$1</p>", $text);
    $text = preg_replace('/###### (.*?)\n/', '<h6>$1</h6>',                       $text);
    $text = preg_replace('/##### (.*?)\n/',  '<h5>$1</h5>',                       $text);
    $text = preg_replace('/#### (.*?)\n/',   '<h4>$1</h4>',                       $text);
    $text = preg_replace('/### (.*?)\n/',    '<h3>$1</h3>',                       $text);
    $text = preg_replace('/## (.*?)\n/',     '<h2>$1</h2>',                       $text);
    $text = preg_replace('/# (.*?)\n/',      '<h1>$1</h1>',                       $text); 
    $text = preg_replace('/ccc (.*?)\n/',    '<center>$1</center>',               $text);
    $text = preg_replace('/---(.*?)\n/',     "<hr>",                              $text);
    $text = preg_replace('/-- (.*?)\n/',     '<br><i>$1</i>',                     $text);
    $text = preg_replace('/- (.*?)\n/',      '<br>$1',                            $text);
    $text = preg_replace('/```(.*?)\n/',     '<b>$1</b>',                         $text);
    $text = preg_replace('/\$(.*)\$/',       "<span class='php_var'>$\$1</span>", $text);
    $text = preg_replace('/;/',              ";\n<br>",                           $text);
    $text = preg_replace('/\*\*\*(.*?)\n/',  "<span class='red'> \$1</span>",     $text);       // 3 звездочки - красный
    $text = preg_replace('/\*\*(.*?)\*\*/', '<span class="tenten">$1</span>',     $text);       // 2 звездочки - синий болд
    $text = preg_replace('/\*(.*?)\*/',     '<span class="bold">$1</span>',       $text);       // 1 звездочка - болд
    $text = preg_replace('/\/\/ (.*?)\n/',    "<span class='comment'>// \$1</span>", $text);
    $text = preg_replace('/!!! (.*?)\n/',    '<div style="display:none">$1</div>',  $text); 
    $text = preg_replace('/___ (.*?)\n/',    '<small>$1</small>',                    $text); 
    
// 
     //$text = preg_replace('/<span class="tenten">Me: <\/span>(.*?)<span class="tenten">chatGPT: <\/span>/s', "<div class='me'>$1<\/div>", $text);
     $text = preg_replace('/<span class="tenten">Me: <\/span>(.*?)<span class="tenten">chatGPT: <\/span>/s', "<span class='me'>$1</span>", $text);
    return $text;
}
// Преобразуем текст в HTML
function convertToHTML($t) {

    $text = markdown_to_html($t);
    // Заменим формулы LaTeX на формат MathJax
    $text = preg_replace('/\$(.*?)\$/', '<span class="mathjax">\\($1\\)</span>', $text);


		return $text;
}
// читаем заголовок
function get_title_subtitle()
{           // получает ничего выдает двухэлементный аррей
    GLOBAL $tex_path, $debug;
    $ret = [];
    $title = 0;
    $_ = file($tex_path);
    // if ($debug) print_mess(__line__ . " \$_ ", $_ );
    for ($i = 0; $i < count($_); $i++) {
        $__ = trim($_[$i]);                  // очищаем от концевых пробелов и других непечатных знаков
        if (strlen($__) > 0) {               // если строка не пустая
            $ret[] = markdown_to_html($__);    // добавляем ее содержимое в рет
            $title++;                          // добавляем номер в возвращаемом аррее
        }
        //  if ($debug) print_mess(__line__ . " \$__ ", $__ );
        if ($title == 2) return $ret;          // если заполнили - возвращаем
    }
    // количество элементов в $title 1 или 0
    if (count($ret) == 0) {
        $ret[] = "";           // добавляем пустой заголовок, если нет в текс документе
        $ret[] = "";           // добавляем пустой подзаголовок, если нет в текс документе
    } else {
        $ret[] = "";           // добавляем пустой подзаголовок, если нет в текс документе
    }
    return $ret;            // безусловный возврат $ret с двумя элементами
}
// debug print
function print_mess($sender, $data){
echo("<div class='debug'> $sender<pre>");
print_r($data);
echo("</pre></did>");

}
/////////////////////////////////////////////////////////
function scanDirectoryForFiles($directory, $extension) {
    // Проверяем, существует ли директория
    if (!@scandir($directory)) {
        return "Ошибка: указанный путь не является директорией!";
    }

    // Получаем список всех файлов и папок в директории
    $files = scandir($directory);

    // Массив для хранения файлов с нужным окончанием
    $filteredFiles = [];

    // Проходим по всем файлам
    foreach ($files as $file) {
        // Игнорируем . и .. (специальные элементы в директориях)
        if ($file !== '.' && $file !== '..') {
            // Проверяем, что файл имеет нужное окончание
            if (substr($file, -strlen($extension)) === $extension) {
                $filteredFiles[] = $file; // Добавляем в результат
            }
        }
    }

    // Возвращаем массив файлов с заданным окончанием
    return $filteredFiles;
}

//     main      //////////
$text = file_get_contents($tex_path);

$i = 0; // Счётчик строк
if($show_row_numbers){
  $text = preg_replace_callback(
      '/\n/',
      function () use (&$i) {
          $i++;
          return "\n $i : ";
      },
      $text
  );
}
$htmlContent = convertToHTML($text);

// if ($debug) print_mess(__line__ . " \$title ", $title );
// if ($debug) print_mess(__line__ . " \$htmlContent ", $htmlContent );
$footer = <<<FOOTER
<div class="container">
<div class = "row">
<footer class="footer mt-auto py-3 bg-dark text-light">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-6">
                <p>Контактная информация:</p>
                <p>Email: example@example.com</p>
                <p>Телефон: +123 456 789</p>
            </div>
            <div class="col-md-6">
                <a href="https://opensource.org/licenses/MIT" target="_blank">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/0c/MIT_logo.svg/1280px-MIT_logo.svg.png" alt="MIT License" style="width: 50px; height: auto;">
             <div class="svg_mit">       
                    <img src="../../../../aekap.api/images/mit_icon.svg" >
             </div>                    
                Лицензия MIT
                 </a>
            </div>
        </div>
    </div>
    </div>
    </div>
</footer>
FOOTER;

$htmlOutput = <<<HTML
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$titles[0]}</title>
    <link href="include/css/bootstrap.min.css" rel="stylesheet">
    <link href="include/css/default.min.css" rel="stylesheet" >
    <script src="include/js/tex-mml-chtml.js"></script>
    <script src="include/js/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>   
    <style>
		body:{font-size:large;}
		.tenten{font-weight:bolder; color:#036;}
		mjx-math{font-size:larger!important; color:#404;}
		ul{list-style: none}
        .mt-4{background-color:#ccf!important; padding:20px;}
        .php_var{color: #006; font-weight:bolder}
        .comment{color: #aaa!important}
        .me{background-color: #eef; border-radius:10px; border:thin solid black;padding: 10px;}
        .chatGPT{background-color: #efe; border-radius:10px; border:thin solid black;padding: 10px}
        .comment{color:#999}
        .red{color:red}
        .bold{font-weight:bold}
        .svg_mit{height: 50px; width:auto;}
	</style>
</head>
<body>

<div class="container mt-4">
  <div class="row">
    <div class="card">

        <div class="card-body">
            <div class="content">
                {$htmlContent}
            </div>
        </div>
    </div>
</div>
</div>
{$footer}
<script src="include/js/bootstrap.bundle.min.js"></script>
<script>
    window.onload = function() {
        MathJax.typeset();
    };
</script>

</body>
</html>
HTML;

// Выводим сгенерированный HTML
echo $htmlOutput;
?>

