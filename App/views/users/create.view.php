<?= loadPartial('head') ?>
<?= loadPartial('navbar') ?>


    <!-- 注册表单框 -->
    <div class="flex justify-center items-center mt-20">
        <div class="bg-white p-8 rounded-lg shadow-md w-full md:w-500 mx-6">
            <h2 class="text-4xl text-center font-bold mb-4">注册</h2>
            <?= loadPartial('errors',[
                'errors' => $errors ?? []
            ]) ?>
            <form method="POST" action="/auth/register">
            <div class="mb-4">
                <input
                type="text"
                name="name"
                placeholder="全名"
                class="w-full px-4 py-2 border rounded focus:outline-none"
                value="<?= $user['name'] ?? '' ?>"
                />
            </div>
            <div class="mb-4">
                <input
                type="email"
                name="email"
                placeholder="电子邮箱地址"
                class="w-full px-4 py-2 border rounded focus:outline-none"
                value="<?= $user['email'] ?? '' ?>"
                />
            </div>
            <div class="mb-4">
                <input
                type="text"
                name="city"
                placeholder="城市"
                class="w-full px-4 py-2 border rounded focus:outline-none"
                value="<?= $user['city'] ?? '' ?>"
                />
            </div>
            <div class="mb-4">
                <input
                type="text"
                name="province"
                placeholder="省份"
                class="w-full px-4 py-2 border rounded focus:outline-none"
                value="<?= $user['province'] ?? '' ?>"
                />
            </div>
            <div class="mb-4">
                <input
                type="password"
                name="password"
                placeholder="密码"
                class="w-full px-4 py-2 border rounded focus:outline-none"
                />
            </div>
            <div class="mb-4">
                <input
                type="password"
                name="password_confirmation"
                placeholder="确认密码"
                class="w-full px-4 py-2 border rounded focus:outline-none"
                />
            </div>
            <button
                type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none"
            >
                注册
            </button>

            <p class="mt-4 text-gray-500">
                已经有账号了？
                <a class="text-blue-900" href="login.html">登录</a>
            </p>
            </form>
        </div>
    </div>

<?= loadPartial('footer') ?>