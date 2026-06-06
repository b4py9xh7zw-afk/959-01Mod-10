<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">已开发票</h1>
    <p class="text-gray-600">查看所有已开具的发票记录。</p>
</div>

<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">发票号码</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">关联订单</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">发票抬头</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">金额</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">类型</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">开票时间</th>
                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">操作</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php if (empty($invoices)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            暂无发票记录
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-900"><?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm text-gray-600"><?php echo htmlspecialchars($invoice['order_no']); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900"><?php echo htmlspecialchars($invoice['company_name']); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-lg font-bold text-gray-900">¥<?php echo number_format($invoice['amount'], 2); ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                    $typeMap = ['electronic' => '电子普票', 'general' => '普通发票', 'special' => '专用发票'];
                                    $typeClass = ['electronic' => 'bg-blue-100 text-blue-800', 'general' => 'bg-green-100 text-green-800', 'special' => 'bg-purple-100 text-purple-800'];
                                ?>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $typeClass[$invoice['invoice_type']] ?? ''; ?>">
                                    <?php echo $typeMap[$invoice['invoice_type']] ?? $invoice['invoice_type']; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php echo htmlspecialchars($invoice['issued_at']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <a href="/invoice/view?id=<?php echo $invoice['id']; ?>" class="text-blue-600 hover:text-blue-800 font-medium">查看详情</a>
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
                    <a href="/invoice/invoices?page=<?php echo $page - 1; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">上一页</a>
                <?php endif; ?>
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="/invoice/invoices?page=<?php echo $i; ?>" 
                       class="px-3 py-2 border rounded-lg text-sm <?php echo $i == $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="/invoice/invoices?page=<?php echo $page + 1; ?>" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">下一页</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
