<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;

class UserController {
    protected $db;

    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    /**
     * 显示登陆页面
     * 此方法加载登录视图。
     * 
     * @return void
     */
    public function login()
    {
        // 调用 loadView 函数来加载登陆页面的视图
        loadView('users/login');
    }

    /**
     * 显示注册页面
     * 此方法加载注册视图。
     * 
     * @return void
     */
    public function create()
    {
        // 调用 loadView 函数来加载创建用户 (注册) 页面的视图
        loadView('users/create');
    }

    /**
     * 将用户数据存储到数据库中
     * 
     * @return void 该方法没有返回值
     */
    public function store()
    {
        // 从 POST 请求中获取用户数据
        $name = $_POST['name'];
        $email = $_POST['email'];
        $city = $_POST['city'];
        $province = $_POST['province'];
        $password = $_POST['password'];
        $passwordConfirmation = $_POST['password_confirmation'];

        // 初始化一个数组来存放可能的错误信息
        $errors = [];

        // 验证电子邮件是否有效
        if (!Validation::email($email)){
            $errors['email'] = '请输入一个合法的电子邮箱地址!';
        }

        // 验证名字是否在2到50字符之间
        if (!Validation::string($name,2,50)){
            $errors['name'] = '姓名长度为2到50!';
        }

        // 验证密码长度至少为6字符
        if (!Validation::string($password,6,50)){
            $errors['password'] = '密码长度至少为6位!';
        }

        // 验证密码和确认密码是否匹配
        if (!Validation::match($password, $passwordConfirmation)){
            $errors['password_confirmation'] = '两次输入的密码不一致!';
        }

        // 检查是否存在验证错误
        if (!empty($errors)){
            // 如果存在错误， 重新加载注册页面并显示错误信息
            loadView('users/create', [
                'errors' => $errors,
                'user' => [
                    'name' => $name,
                    'email' => $email,
                    'city' => $city,
                    'province' => $province
                ]
            ]);
            // 终止进一步执行
            exit;
        }else{
            // 检查电子邮件是否已存在
            $params = [
                'email' => $email // 使用电子邮件作为查询参数
            ];

            // 执行查询，查看是否有相同的电子邮件已存在于数据库中
            $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();

            // 如果查询到用户 (即电子邮件已被注册)
            if ($user){
                $errors['email'] = '该邮箱已注册!'; // 添加错误信息
                // 重新加载注册页面并显示错误信息
                loadView('users/create', [
                    'errors' => $errors,
                ]);
                // 终止进一步执行
                exit;
            }

            // 创建用户账户
            $params = [
                'name' => $name, // 用户名
                'email' => $email, // 电子邮件
                'city' => $city, // 城市
                'province' => $province, // 州
                'password' => password_hash($password, PASSWORD_DEFAULT) // 对密码进行哈希处理
            ];

            // 执行 SQL 插入操作，将新用户数据插入数据库
            $this->db->query('INSERT INTO users (name, email, city, province, password) VALUES (:name, :email, :city, 
            :province, :password)', $params);

            // Get new user ID
            $userId = $this->db->conn->lastInsertId();

            Session::set('user',[
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'city' => $city,
                'province' => $province
            ]);
            
            // 创建用户成功后重定向到首页
            redirect('/');
        }
    }

    /**
     * 注销用户并结束会话
     * 
     * @return void 无返回值
     */
    public function logout()
    {
        // 调用 Session 类的 clearAll 方法来清空所有会话数据
        Session::clearAll();

        // 获取会话 cookie 的参数
        $params = session_get_cookie_params();
        // 删除会话 cookie，设置过期时间为当前时间之前一天，确保浏览器删除该 cookie
        setcookie('PHPSESSID','',time() - 86400,$params['path'],$params['domain']);

        // 重定向到网站首页
        redirect('/');
    }

    /**
     * 使用电子邮件和密码验证用户身份
     * 此方法首先进行输入认证，然后检查数据库中是否存在该电子邮件的用户，
     * 并验证密码是否正确。如果验证成功，用户信息将被存储在会话中，并重定向到首页。
     * 
     * @return void 无返回值
     */
    public function authenticate()
    {
        // 从 POST 数据中获取电子邮件和密码
        $email = $_POST['email'];
        $password = $_POST['password'];

        // 初始化错误数组
        $errors = [];

        // 验证电子邮件格式
        if (!Validation::email($email)) {
            $errors['email'] = '请输入合法的邮箱地址!';
        }

        // 验证密码长度 (至少6个字符，最多50个字符)
        if (!Validation::string($password, 6, 50)) {
            $errors['password'] = '密码为至少6位!';
        }

        // 如果存在验证错误，重新加载登录页面并显示错误信息
        if (!empty($errors)) {
            loadView('users/login', [
                'errors' => $errors
            ]);
            exit;
        }

        // 准备查询参数，查找电子邮件
        $params = [
            'email' => $email
        ];

        // 从数据库查询用户
        $user = $this->db->query('SELECT * FROM users WHERE email = :email', $params)->fetch();

        // 如果未找到用户，说明电子邮件不正确
        if (!$user) {
            $errors['email'] = '用户不存在或密码错误! ';
            loadView('users/login', [
                'errors' => $errors
            ]);
            exit;
        }

        // 验证密码是否正确
        if (!password_verify($password, $user->password)) {
            $errors['email'] = '用户不存在或密码错误! ';
            loadView('users/login', [
                'errors' => $errors
            ]);
            exit;
        }
        
        // 验证通过，设置用户会话信息
        Session::set('user',[
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'city' => $user->city,
            'province' => $user->province
        ]);

        // 登录成功后重定向到首页
        redirect('/');
    }
}