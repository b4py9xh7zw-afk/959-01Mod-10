<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-800 mb-2">我的订单</h1>
        <p class="text-gray-600">查看所有订单记录及开票状态。</p>
    </div>
    <a href="/invoice/order/create" 
       class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
        + 新订单
    </a>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">订单号</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">类型</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">产品</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">金额</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">状态</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">开票状态</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">创建时间</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            暂无订单记录
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-900"><?php echo htmlspecialchars($order['order_no']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $typeMap = ['purchase' => '新购', 'renewal' => '续费', 'additional' => '增购'];
                                    $typeClass = ['purchase' => 'bg-blue-100 text-blue-800', 'renewal' => 'bg-green-100 text-green-800', 'additional' => 'bg-purple-100 text-purple-800'];
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $typeClass[$order['order_type']] ?? ''; ?>">
                                    <?php echo $typeMap[$order['order_type']] ?? $order['order_type']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900"><?php echo htmlspecialchars($order['product_name']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-900">¥<?php echo number_format($order['amount'], 2); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $statusMap = ['pending' => '待处理', 'completed' => '已完成', 'cancelled' => '已取消'];
                                    $statusClass = ['pending' => 'bg-yellow-100 text-yellow-800', 'completed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-gray-100 text-gray-800'];
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass[$order['status']] ?? ''; ?>">
                                    <?php echo $statusMap[$order['status']] ?? $order['status']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($order['invoice_generated']): ?>
                                    <?php 
                                        $invStatusMap = ['pending' => '待审核', 'approved' => '已通过', 'rejected' => '已驳回', 'issued' => '已开票'];
                                        $invStatusClass = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-blue-100 text-blue-800', 'rejected' => 'bg-red-100 text-red-800', 'issued' => 'bg-green-100 text-green-800'];
                                        $invStatus = $order['invoice_status'] ?? 'pending';
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $invStatusClass[$invStatus] ?? ''; ?>">
                                        <?php echo $invStatusMap[$invStatus] ?? $invStatus; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">未申请</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($order['created_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if (!$order['invoice_generated'] && $order['status'] === 'completed'): ?>
                                    <form action="/invoice/order/generate-invoice" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                        <button type="submit" class="text-blue-600 hover:text-blue-800 font-medium">申请开票</button>
                                    </form>
                                <?php elseif ($order['invoice_generated']): ?>
                                    <a href="/invoice/applications?order_id=<?php echo $order['id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium">查看申请</a>
                                <?php endif; ?>
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
                <?php if ($page > 1): ?>
                    <a href="/invoice/orders?page=<?php echo $page - 1; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">上一页</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="/invoice/orders?page=<?php echo $i; ?>" 
                       class="px-3 py-2 border rounded-lg text-sm <?php echo $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="/invoice/orders?page=<?php echo $page + 1; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">下一页</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
