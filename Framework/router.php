<?php

namespace Framework;

use App\Controllers\ErrorController;
use Framework\Middleware\Authorise;

class Router{
    protected $routes = [];

    /**
     * 注册一条路由规则
     * 
     * @param string $method
     * @param string $uri
     * @param string $action
     * @param array $middleware
     * @return void
     */
    private function registerRoute($method,$uri,$action,$middleware = []){

        list($controller, $controllerMethod) = explode('@', $action);
        

        $this->routes[] = [
            'method' => $method,
            'uri' => $uri,
            'controller' => $controller,
            'controllerMethod' => $controllerMethod,
            'middleware' => $middleware
        ];
    }
     
    /**
     * 添加一个GET路由
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function addGet($uri,$controller,$middleware = []){
        $this->registerRoute('GET',$uri,$controller,$middleware);
    }

    /**
     * 添加一个POST路由
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function addPost($uri,$controller,$middleware = []){
        $this->registerRoute('POST',$uri,$controller,$middleware);
    }

    /**
     * 添加一个PUT路由
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function addPut($uri,$controller,$middleware = []){
        $this->registerRoute('PUT',$uri,$controller,$middleware);
    }

    /**
     * 添加一个DELETE路由
     * 
     * @param string $uri
     * @param string $controller
     * @param array $middleware
     * @return void
     */
    public function addDelete($uri,$controller,$middleware = []){
        $this->registerRoute('DELETE',$uri,$controller,$middleware);
    }

    /**
     * 加载错误页面
     * @param int $httpCode
     * @return void
     */
    public function error($httpCode = 404)
    {
        http_response_code($httpCode);
        loadView("error/{$httpCode}");
        exit;
    }

    /**
     * 执行路由
     * @param string $uri

     * @return void
     */
    public function route($uri){

        $requestMethod = $_SERVER['REQUEST_METHOD'];

        // 检查 POST 请求中的特殊字段 _method
        if ($requestMethod === 'POST' && isset($_POST['_method'])){
            // 重写请求方法
            $requestMethod = strtoupper($_POST['_method']);
        }

        // 拆分目前URI
        $uriSegments = explode('/',trim($uri,'/'));

        foreach($this->routes as $route){

            // 拆分路由URI
            $routeSegments = explode('/',trim($route['uri'],'/'));

            $match = false;

            // 检查拆分后的字符串片段数量是否匹配
            if (count($uriSegments) === count($routeSegments) && strtoupper($route['method'] === $requestMethod)){
                $params = [];

                $match = true;

                for ($i = 0; $i < count($uriSegments); $i++) {
                    // 如果有uri不匹配或者参数不存在
                    if ($routeSegments[$i] !== $uriSegments[$i] && !preg_match('/\{(.+?)\}/', $routeSegments[$i])){
                        $match = false;
                        break;
                    }

                    // 检查并且提取数字
                    if (preg_match('/\{(.+?)\}/', $routeSegments[$i],$matches)){
                        $params[$matches[1]] = $uriSegments[$i];
                    }
                }
            }

            if($match){

                foreach ($route['middleware'] as $middleware){
                    (new Authorise())->handle($middleware);
                }

                $controller = 'App\\Controllers\\' . $route['controller'];
                $controllerMethod = $route['controllerMethod'];
                
                // 实例化控制器和调用方法
                $controllerInstance = new $controller();
                $controllerInstance->$controllerMethod($params);
                return;
            }
        }

        ErrorController::notFound();
    }
}
    
    
   
