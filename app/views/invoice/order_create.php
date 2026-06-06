<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">创建订单</h1>
    <p class="text-gray-600">创建新的订单，完成后系统将自动生成开票申请。</p>
</div>

<div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl">
    <form action="/invoice/order/create" method="POST" class="space-y-6">
        <?php if ($_SESSION['role'] === 'admin' && !empty($users)): ?>
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">所属用户</label>
                <select id="user_id" name="user_id" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" <?php echo $user['id'] == $_SESSION['user_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?> (<?php echo htmlspecialchars($user['email']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>

        <div>
            <label for="product_name" class="block text-sm font-medium text-gray-700 mb-2">
                产品名称 <span class="text-red-500">*</span>
            </label>
            <input type="text" id="product_name" name="product_name" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                   placeholder="请输入产品名称" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="order_type" class="block text-sm font-medium text-gray-700 mb-2">
                    订单类型 <span class="text-red-500">*</span>
                </label>
                <select id="order_type" name="order_type" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <option value="purchase">新购</option>
                    <option value="renewal">续费</option>
                    <option value="additional">增购</option>
                </select>
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                    金额（元） <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-500">¥</span>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01"
                           class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="0.00" required>
                </div>
            </div>
        </div>

        <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">自动开票</h3>
                    <p class="text-sm text-yellow-700 mt-1">订单创建完成后，系统将自动生成开票申请。请确保发票资料已填写完整。</p>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4 pt-4">
            <a href="/invoice/orders" class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all">
                取消
            </a>
            <button type="submit" 
                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                创建订单
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
