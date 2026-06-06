<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">开票申请</h1>
    <p class="text-gray-600">查看和管理所有开票申请。</p>
</div>

<?php if ($_SESSION['role'] === 'admin'): ?>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <a href="/invoice/applications?status=pending" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all <?php echo ($status ?? '') === 'pending' ? 'ring-2 ring-yellow-500' : ''; ?>">
            <div class="text-3xl font-bold text-yellow-600"><?php echo $pendingCount; ?></div>
            <div class="text-gray-600 mt-1">待审核</div>
        </a>
        <a href="/invoice/applications?status=approved" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all <?php echo ($status ?? '') === 'approved' ? 'ring-2 ring-blue-500' : ''; ?>">
            <div class="text-3xl font-bold text-blue-600"><?php echo $approvedCount; ?></div>
            <div class="text-gray-600 mt-1">已通过</div>
        </a>
        <a href="/invoice/applications?status=rejected" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all <?php echo ($status ?? '') === 'rejected' ? 'ring-2 ring-red-500' : ''; ?>">
            <div class="text-3xl font-bold text-red-600"><?php echo $rejectedCount; ?></div>
            <div class="text-gray-600 mt-1">已驳回</div>
        </a>
        <a href="/invoice/applications?status=issued" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all <?php echo ($status ?? '') === 'issued' ? 'ring-2 ring-green-500' : ''; ?>">
            <div class="text-3xl font-bold text-green-600"><?php echo $issuedCount; ?></div>
            <div class="text-gray-600 mt-1">已开票</div>
        </a>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">申请ID</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">关联订单</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">产品</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">金额</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">状态</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">申请时间</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            暂无开票申请
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-900">#<?php echo $app['id']; ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-600"><?php echo htmlspecialchars($app['order_no']); ?></span>
                                <?php 
                                    $typeMap = ['purchase' => '新购', 'renewal' => '续费', 'additional' => '增购'];
                                    $typeClass = ['purchase' => 'bg-blue-100 text-blue-800', 'renewal' => 'bg-green-100 text-green-800', 'additional' => 'bg-purple-100 text-purple-800'];
                                ?>
                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium <?php echo $typeClass[$app['order_type']] ?? ''; ?>">
                                    <?php echo $typeMap[$app['order_type']] ?? $app['order_type']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900"><?php echo htmlspecialchars($app['product_name']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-900">¥<?php echo number_format($app['amount'], 2); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $statusMap = ['pending' => '待审核', 'approved' => '已通过', 'rejected' => '已驳回', 'issued' => '已开票'];
                                    $statusClass = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-blue-100 text-blue-800', 'rejected' => 'bg-red-100 text-red-800', 'issued' => 'bg-green-100 text-green-800'];
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass[$app['status']] ?? ''; ?>">
                                    <?php echo $statusMap[$app['status']] ?? $app['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($app['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="/invoice/application/view?id=<?php echo $app['id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium">查看详情</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                共 <?php echo $total; ?> 条记录，第 <?php echo $page; ?> / <?php echo $totalPages; ?> 页
            </div>
            <div class="flex space-x-2">
                <?php 
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = !empty($queryParams) ? '&' . http_build_query($queryParams) : '';
                ?>
                <?php if ($page > 1): ?>
                    <a href="/invoice/applications?page=<?php echo $page - 1 . $queryString; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">上一页</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="/invoice/applications?page=<?php echo $i . $queryString; ?>" 
                       class="px-3 py-2 border rounded-lg text-sm <?php echo $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="/invoice/applications?page=<?php echo $page + 1 . $queryString; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">下一页</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
