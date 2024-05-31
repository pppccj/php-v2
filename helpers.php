<?php

/**
*获得根路径
*
*@param string $path
*@return string
*/
function basePath($path = '')
{
    return __DIR__ . '/' .$path;
}

/**
 * 加载部分视图
 * 
 * @param string $name
 * @return void
 */
function loadPartial($name,$data = []){
    $partialPath = basePath("App/views/partials/{$name}.php");

    if(file_exists($partialPath)){
        extract($data);
        require $partialPath;
    }else{
       echo "{$name}部分视图不存在";
    }
}

/**
 * 加载视图
 * 
 * @param string $name
 * @param array $data
 * @return void
 */
function loadView($name,$data = []){
    $viewPath = basePath("App/views/{$name}.view.php");

    if(file_exists($viewPath)){
        extract($data);
        require $viewPath;
    }else{
       echo "{$path}视图不存在!";
    }
}

/**
 * 检查某个值
 * 
 * @param string $value
 * @return void
 */
function inspect($value){
    echo "<pre>";
    var_dump($value);
    echo "<pre>";
}

/**
 * @param mixed $name
 * @return void
 */
function inspectAndDie($value){
    echo '<pre>';
    die(var_dump($value));
    echo '<pre>';
}

/**
 * 清洗数据
 * 
 * @param string $dirty 待清洗的原始字符串数据
 * @return string 返回清洗后的安全字符串
 */
function sanitize($dirty){
    return filter_var(trim($dirty),FILTER_SANITIZE_SPECIAL_CHARS);
}

/**
 * 重定向到给定的 URL
 * 
 * @param string $url 目标 URL 地址
 * @return void 该函数没有返回值。
 */
function redirect($url)
{
    header("Location: {$url}");
    exit;
}


 