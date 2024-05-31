<?= loadPartial('head') ?>
<?= loadPartial('navbar') ?>

    <!-- 登录表单框 -->
    <div class="flex justify-center items-center mt-20">
        <div class="bg-white p-8 rounded-lg shadow-md w-full md:w-500 mx-6">
            <h2 class="text-4xl text-center font-bold mb-4">登录</h2>
            
            <?= loadPartial('errors',[
                'errors' => $errors ?? []
            ]) ?>

            <form method="POST" action="/auth/login">
            <div class="mb-4">
                <input
                type="email"
                name="email"
                placeholder="电子邮箱地址"
                class="w-full px-4 py-2 border rounded focus:outline-none"
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
            <button
                type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded focus:outline-none"
            >
                登录
            </button>

            <p class="mt-4 text-gray-500">
                没有账号？
                <a class="text-blue-900" href="register.html">注册</a>
            </p>
            </form>
        </div>
    </div>

<?= loadPartial('footer') ?>