<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">开票申请详情</h1>
            <p class="text-gray-600">申请编号: #<?php echo $application['id']; ?></p>
        </div>
        <a href="/invoice/applications" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
            ← 返回列表
        </a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">申请信息</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-gray-500">关联订单</span>
                    <p class="font-mono text-gray-900"><?php echo htmlspecialchars($application['order_no']); ?></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">订单类型</span>
                    <p><?php 
                        $typeMap = ['purchase' => '新购', 'renewal' => '续费', 'additional' => '增购'];
                        echo $typeMap[$application['order_type']] ?? $application['order_type'];
                    ?></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">产品名称</span>
                    <p class="text-gray-900"><?php echo htmlspecialchars($application['product_name']); ?></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">申请金额</span>
                    <p class="text-xl font-bold text-blue-600">¥<?php echo number_format($application['amount'], 2); ?></p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">申请状态</span>
                    <p>
                        <?php 
                            $statusMap = ['pending' => '待审核', 'approved' => '已通过', 'rejected' => '已驳回', 'issued' => '已开票'];
                            $statusClass = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-blue-100 text-blue-800', 'rejected' => 'bg-red-100 text-red-800', 'issued' => 'bg-green-100 text-green-800'];
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass[$application['status']] ?? ''; ?>">
                            <?php echo $statusMap[$application['status']] ?? $application['status']; ?>
                        </span>
                    </p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">申请时间</span>
                    <p class="text-gray-900"><?php echo htmlspecialchars($application['created_at']); ?></p>
                </div>
                <?php if ($application['reviewed_at']): ?>
                    <div>
                        <span class="text-sm text-gray-500">审核人</span>
                        <p class="text-gray-900"><?php echo htmlspecialchars($application['reviewer_name'] ?? '-'); ?></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">审核时间</span>
                        <p class="text-gray-900"><?php echo htmlspecialchars($application['reviewed_at']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($application['status'] === 'rejected' && $application['rejection_reason']): ?>
            <div class="bg-red-50 border border-red-200 rounded-xl p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h3 class="text-lg font-semibold text-red-800 mb-2">驳回原因</h3>
                        <p class="text-red-700 whitespace-pre-wrap"><?php echo htmlspecialchars($application['rejection_reason']); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'admin' && $application['status'] === 'pending'): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-yellow-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-yellow-800 mb-2">资料完整性检查</h3>
                        <?php if (empty($missingFields)): ?>
                            <p class="text-green-600 font-medium">✓ 资料完整，可以审核通过</p>
                        <?php else: ?>
                            <p class="text-yellow-700 mb-2">当前缺少以下资料：</p>
                            <ul class="list-disc list-inside text-yellow-700 space-y-1">
                                <?php foreach ($missingFields as $field): ?>
                                    <li><?php echo htmlspecialchars($field); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">客户发票资料</h2>
            <?php if (!$invoiceInfo): ?>
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    客户尚未填写发票资料
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <span class="text-sm text-gray-500">发票抬头</span>
                        <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($invoiceInfo['company_name']); ?></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">税号</span>
                        <p class="font-mono text-gray-900"><?php echo htmlspecialchars($invoiceInfo['tax_id']); ?></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">开票邮箱</span>
                        <p class="text-gray-900"><?php echo htmlspecialchars($invoiceInfo['invoice_email']); ?></p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-500">开票偏好</span>
                        <p><?php 
                            $prefMap = ['electronic' => '电子普通发票', 'general' => '增值税普通发票', 'special' => '增值税专用发票'];
                            echo $prefMap[$invoiceInfo['invoice_preference']] ?? $invoiceInfo['invoice_preference'];
                        ?></p>
                    </div>
                    <?php if ($invoiceInfo['invoice_preference'] === 'special'): ?>
                        <div class="md:col-span-2 border-t pt-4 mt-2">
                            <h3 class="font-medium text-gray-800 mb-3">专用发票附加信息</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <span class="text-sm text-gray-500">公司地址</span>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($invoiceInfo['address'] ?? '-'); ?></p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">公司电话</span>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($invoiceInfo['phone'] ?? '-'); ?></p>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="text-sm text-gray-500">开户银行</span>
                                    <p class="text-gray-900"><?php echo htmlspecialchars($invoiceInfo['bank_name'] ?? '-'); ?></p>
                                </div>
                                <div class="md:col-span-2">
                                    <span class="text-sm text-gray-500">银行账号</span>
                                    <p class="font-mono text-gray-900"><?php echo htmlspecialchars($invoiceInfo['bank_account'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">操作</h2>
            
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <?php if ($application['status'] === 'pending'): ?>
                    <form action="/invoice/application/approve" method="POST" class="mb-3">
                        <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition-all">
                            ✓ 通过审核
                        </button>
                    </form>
                    
                    <button onclick="toggleRejectForm()" 
                            class="w-full px-4 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-all">
                        ✗ 驳回申请
                    </button>
                    
                    <div id="reject_form" class="hidden mt-4">
                        <form action="/invoice/application/reject" method="POST">
                            <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">驳回原因（必填）</label>
                                <textarea name="rejection_reason" rows="4" required
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
                                          placeholder="请详细说明缺少什么资料..."></textarea>
                                <p class="text-xs text-gray-500 mt-1">例如：缺少公司地址和电话信息；税号格式不正确</p>
                            </div>
                            <button type="submit" 
                                    class="w-full px-4 py-3 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-all">
                                确认驳回
                            </button>
                        </form>
                    </div>
                <?php elseif ($application['status'] === 'approved'): ?>
                    <button onclick="toggleIssueForm()" 
                            class="w-full px-4 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-all">
                        📄 开具发票
                    </button>
                    
                    <div id="issue_form" class="hidden mt-4">
                        <form action="/invoice/application/issue" method="POST">
                            <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">发票号码（必填）</label>
                                <input type="text" name="invoice_number" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                       placeholder="请输入发票号码">
                            </div>
                            <button type="submit" 
                                    class="w-full px-4 py-3 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-all">
                                确认开票
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <?php if ($application['status'] === 'rejected'): ?>
                    <form action="/invoice/application/resubmit" method="POST">
                        <input type="hidden" name="id" value="<?php echo $application['id']; ?>">
                        <button type="submit" 
                                class="w-full px-4 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all">
                            ↻ 重新提交
                        </button>
                    </form>
                    <a href="/invoice/info" class="block mt-3 text-center px-4 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-all">
                        编辑发票资料
                    </a>
                <?php elseif ($application['status'] === 'pending'): ?>
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <p class="text-yellow-700 text-sm">等待财务审核中...</p>
                    </div>
                <?php elseif ($application['status'] === 'approved'): ?>
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <p class="text-blue-700 text-sm">审核已通过，等待开票...</p>
                    </div>
                <?php elseif ($application['status'] === 'issued'): ?>
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <p class="text-green-700 text-sm">发票已开具</p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleRejectForm() {
    document.getElementById('reject_form').classList.toggle('hidden');
}
function toggleIssueForm() {
    document.getElementById('issue_form').classList.toggle('hidden');
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
