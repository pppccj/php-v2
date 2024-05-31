<?php

namespace App\Controllers;

use Framework\Database;
use Framework\Validation;
use Framework\Session;
use Framework\Authorisation;
use Framework\Middleware\Authorise;

class ListingController
{
    protected $db;

    public function __construct()
    {
        $config = require basePath('config/db.php');
        $this->db = new Database($config);
    }

    /**
     * 展示所有岗位
     * 
     * @return void
     */
    public function index()
    {
        $listings = $this->db->query('SELECT * FROM listing ORDER BY created_at DESC')->fetchAll();

        loadView('listings/index', [
            'listings' => $listings
        ]);
    }

    /**
     * 展示创建职位表单
     * 
     * @return void
     */
    public function create()
    {
        loadView('listings/create');
    }

    /**
     * 展示单一岗位页面
     * 
     * @return void
     */
    public function show($params)
    {

        $id = $params['id'] ?? '';

        $params = [
            'id' => $id
        ];

        $listing = $this->db->query('SELECT * FROM listing WHERE id = :id', $params)->fetch();

        // 检查 listing 是否存在
        if (!$listing) {
            ErrorController::notFound('该岗位不存在! ');
            return;
        }

        loadView('listings/show', [
            'listing' => $listing
        ]);
    }

    /**
     * 在数据库中存储数据
     * 
     * @return void 该函数没有返回值
     */
    public function store()
    {
        $allowedFields = [
            'title', 'description', 'salary', 'tags', 'company', 'address', 'city', 'province', 'phone',
            'email', 'requirements', 'benefits'
        ];

        // 从$_POST中提取允许的字段
        $newListingData = array_intersect_key($_POST, array_flip($allowedFields));

        // 设置用户ID
        $newListingData['user_id'] = Session::get('user')['id'];

        // 清洗数据
        $newListingData = array_map('sanitize', $newListingData);

        // 定义必须的字段
        $requiredFields = ['title', 'description', 'email', 'city', 'province'];

        // 检查必须的字段
        $errors = [];
        foreach ($requiredFields as $field) {
            if (empty($newListingData[$field]) || !Validation::string($newListingData[$field])) {
                $errors[$field] = ucfirst($field) . '为必需项';
            }
        }

        // 处理可能的错误
        if (!empty($errors)) {
            // 如果有错误，重新加载创建列表的视图并显示错误
            loadView('listings/create', [
                'errors' => $errors,
                'listing' => $newListingData
            ]);
        } else {
            //初始化一个数组来收集所有字段名称，为构建SQL查询做准备
            $field = [];

            // 遍历 $newListingData 数组，收集字段名称
            foreach ($newListingData as $field => $value) {
                // 向 $field 数组的末尾添加一个新元素
                $fields[] = $field;
            }

            // 使用 implode 函数将字段名称数组转换为字符串，用逗号分离
            $fields = implode(',', $fields);

            // 初始化一个数组来收集对应的占位符
            $values = [];

            // 遍历 $newListingData 数组，为每个字段生成一个占位符，并处理空字符串为 null
            foreach ($newListingData as $field => $value) {
                if ($value === '') {
                    $newListingData[$field] = null;
                }
                $values[] = ':' . $field;
            }

            // 使用 implode 函数将占位符数组转换为字符串，用逗号分离
            $values = implode(',', $values);

            // 构建 SQL 插入语句
            $query = "INSERT INTO listing ({$fields}) VALUES ({$values})";

            // 执行 SQL 插入操作
            $this->db->query($query, $newListingData);

            Session::setFlashMessage('success_message','已成功创建职位! ');

            // 重定向到列表页面
            redirect('/listings');
        }
    }

    /**
     * 删除一个列表项
     * 
     * @param array $params 包含必要参数的数组，如列表项的ID
     * @return void 该方法没有返回值。
     */
    public function destroy($params)
    {
        // 从参数中获取列表项的ID
        $id = $params['id'];

        // 准备用于数据库查询的参数
        $params = [
            'id' => $id
        ];

        // 查询数据库以确认列表项是否存在
        $listing = $this->db->query('SELECT * FROM listing WHERE id = :id', $params)->fetch();

        // 如果查询结果为空，则列表项不存在
        if (!$listing) {
            // 调用错误控制器处理找不到列表项的情况
            ErrorController::notFound('职位不存在! ');
            return;
        }

        if (!Authorisation::isOwner($listing->user_id)){
            inspect($_SESSION);
            Session::setFlashMessage('error_message','你没有权限删除此职位! ');
            return redirect('/listings/' . $listing->id);
        }

        // 执行删除操作
        $this->db->query('DELETE FROM listing WHERE id = :id', $params);

        // 设置提示信息
        Session::setFlashMessage('error_message','删除职位成功! ');

        // 删除成功后重定向到列表页面
        redirect('/listings');
    }

    /**
     * 显示列表项编辑表单
     * 
     * @param array $params 包含必要参数的数组，例如列表项的ID
     * @return void 该方法没有返回值。
     */
    public function edit($params)
    {
        // 尝试从参数中获取ID，如果没有提供，则默认为空字符串
        $id = $params['id'] ?? '';

        // 准备用于数据库查询的参数
        $params = [
            'id' => $id
        ];

        // 查询数据库,获取指定ID的列表项数据
        $listing = $this->db->query('SELECT * FROM listing WHERE id = :id', $params)->fetch();

        // 检查查询结果，确保列表项存在
        if (!$listing) {
            // 如果未找到列表项，调用错误控制器并返回
            ErrorController::notFound('职位不存在! ');
            return;
        }

        // 授权
        if (!Authorisation::isOwner($listing->user_id)){
            Session::setFlashMessage('error_message','你没有权限修改此职位! ');
            return redirect('/listings/' . $listing->id);
        }

        // 如果列表项存在，加载编辑视图并传递列表项数据
        loadView('listings/edit', [
            'listing' => $listing
        ]);
    }

    /**
     * 更新列表项数据
     * 
     * @param array $params
     * @return void
     */
    public function update($params)
    {
        // 从参数中尝试获取列表项ID，如果没有提供，则默认为空字符串
        $id = $params['id'] ?? '';

        // 准备用于数据库查询的参数
        $params = [
            'id' => $id
        ];

        // 查询数据库以确认列表项是否存在
        $listing = $this->db->query('SELECT * FROM listing WHERE id = :id', $params)->fetch();

        // 如果列表项不存在
        if (!$listing) {
            // 调用错误控制器并结束方法执行
            ErrorController::notFound('职位不存在! ');
            return;
        }

        // 授权
        if (!Authorisation::isOwner($listing->user_id)){
            Session::setFlashMessage('error_message','你没有权限修改此职位! ');
            return redirect('/listings/' . $listing->id);
        }

        // 定义可以从表单接受的字段列表
        $allowedFields = ['title', 'description', 'salary', 'tags', 'company', 'address', 'city','province','phone',
        'email','requirements','benefits'];

        // 过滤并保留允许的字段
        $updateValues = array_intersect_key($_POST, array_flip($allowedFields));

        // 对过滤后的数据进行清洗
        $updateValues = array_map('sanitize', $updateValues);

        // 定义必须填写的字段列表
        $requiredFields = ['title','description','salary','email','city','province'];
        
        // 初始化错误收集数组
        $errors = [];

        // 检查必填字段是否已填写且符合要求
        foreach ($requiredFields as $field) {
            if (empty($updateValues[$field]) || !Validation::string($updateValues[$field])) {
                $errors[$field] = ucfirst($field) . '为必须项!';
            }
        }

        // 如果存在错误
        if (!empty($errors)) {
            // 重新加载编辑视图并传递错误信息与当前列表数据
            loadView('listings/edit', [
                'listing' => $listing,
                'errors' => $errors
            ]);
            exit;
        }else{
            // 构建 SQL 更新语句中的字段赋值部分
            $updateFields = [];
            foreach (array_keys($updateValues) as $field) {
                $updateFields[] = "{$field} = :{$field}";
            }

            // 将字段赋值部分合成字符串
            $updateFields = implode(', ', $updateFields);

            // 构建完整的 SQL 更新语句
            $updateQuery = "UPDATE listing SET $updateFields WHERE id = :id";

            // 在更新数据中包括 ID
            $updateValues['id'] = $id;

            // 执行 SQL 更新操作
            $this->db->query($updateQuery, $updateValues);

            // 设置成功信息
            Session::setFlashMessage('success_message','职位信息已更新!');

            // 重定向到列表项详情页面
            redirect('/listings/' . $id);
        }
    }

    /**
     * 根据关键词和地点搜索列表
     * 此方法从请求中获取关键词和地点，然后执行数据库查询以找到匹配的列表项。
     * 查询结果用于渲染列表视图。
     * 
     * @return void 无返回值。
     */
    public function search()
    {
        // 从 GET 请求中获取关键词和地点，如果没有提供则默认为空字符串
        $keywords = isset($_GET['keywords']) ? trim($_GET['keywords']) : '';
        $location = isset($_GET['location']) ? trim($_GET['location']) : '';

        // 构建 SQL 查询，搜索标题、描述、标签或公司名中包含关键词，并且城市或州包含地点的列表
        $query = "SELECT * FROM listing WHERE
                 (title LIKE :keywords OR description LIKE :keywords OR 
                 tags LIKE :keywords OR company LIKE :keywords) AND
                 (city LIKE :location OR province LIKE :location)";

        // 准备查询参数，关键词和地点周围添加百分号以实现模糊匹配
        $params = [
            'keywords' => "%{$keywords}%",
            'location' => "%{$location}%"
        ];

        // 执行查询并获取所有匹配的记录
        $listings = $this->db->query($query, $params)->fetchAll();

        // 加载列表视图，并传递查询到的列表数据及搜索条件
        loadView('/listings/index', [
            'listings' => $listings,
            'keywords' => $keywords,
            'location' => $location
        ]);
    }
}