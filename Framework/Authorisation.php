<?php

namespace Framework;

use Framework\Session;

/**
 * 授权类
 */
class Authorisation
{
    /**
     * 检查当前登录的用户是否拥有指定资源的所有权
     * 
     * @param int $resourceId 需要检查所有权的资源ID
     * @return bool 返回当前登录用户是否为该资源的所有者
     */
    public static function isOwner($resourceId)
    {
        // 从会话中获取当前用户信息
        $sessionUser = Session::get('user');
        
        // 检查会话中是否存在用户信息并且用户ID是否已设置
        if ($sessionUser !== null && isset($sessionUser['id'])) {
            // 将会话中的用户ID转换为整数
            $sessionUserId = (int) $sessionUser['id'];
            // 比较会话中的用户ID与传入的资源ID是否相同
            return $sessionUserId === $resourceId;
        }
        
        // 如果会话中没有用户信息或用户ID没有设置，返回false
        return false;
    }
}