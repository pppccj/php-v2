<?php

namespace Framework\Middleware;

use Framework\Session;

/**
 * 认证授权中间件类
 * 用于检查用户是否认证，并根据角色重定向用户。
 */
class Authorise
{
    /**
     * 检查用户是否已经认证
     * 
     * @return bool 返回用户是否认证的布尔值
     */
    public function isAuthenticated()
    {
        // 检查会话中是否有用户信息
        return Session::has('user');
    }

    /**
     * 处理用户的请求
     * 根据用户角色和认证状态进行适当的重定向。
     * 
     * @param string $role 需要检查的用户角色
     * @return bool 
     */
    public function handle($role)
    {
        // 如果角色是 'guest' (访客) 并且用户已经认证，重定向到首页
        if ($role === 'guest' && $this->isAuthenticated()) {
            return redirect('/');
        }
        
        // 如果角色是 'auth' (需认证用户) 并且用户未认证，重定向到登录页面
        elseif ($role === 'auth' && !$this->isAuthenticated()) {
            return redirect('/auth/login');
        }
    }
}