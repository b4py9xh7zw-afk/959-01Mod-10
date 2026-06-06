<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">发票详情</h1>
            <p class="text-gray-600">发票号码: <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
        </div>
        <a href="/invoice/invoices" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors">
            ← 返回列表
        </a>
    </div>
</div>

<?php if ($hasChanges): ?>
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-amber-800">注意：发票数据为快照</h3>
                <p class="text-sm text-amber-700 mt-1">客户的发票资料已发生变更，但本发票为已开票的历史数据快照，内容保持不变。</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">增值税发票</h2>
                    <p class="text-gray-500 mt-1">INVOICE</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-gray-500">发票日期</div>
                    <div class="text-gray-900 font-medium"><?php echo date('Y年m月d日', strtotime($invoice['issued_at'])); ?></div>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6 mb-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <div class="text-sm text-gray-500 mb-1">购买方</div>
                        <div class="font-semibold text-gray-900 text-lg"><?php echo htmlspecialchars($invoice['company_name']); ?></div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500 mb-1">发票号码</div>
                        <div class="font-mono font-semibold text-blue-600 text-lg"><?php echo htmlspecialchars($invoice['invoice_number']); ?></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-8">
                <div>
                    <div class="text-sm text-gray-500 mb-1">纳税人识别号</div>
                    <div class="font-mono text-gray-900"><?php echo htmlspecialchars($invoice['tax_id']); ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">开票邮箱</div>
                    <div class="text-gray-900"><?php echo htmlspecialchars($invoice['invoice_email']); ?></div>
                </div>
                <?php if ($invoice['invoice_type'] === 'special'): ?>
                    <div class="md:col-span-2 border-t pt-4 mt-2">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-gray-500 mb-1">地址</div>
                                <div class="text-gray-900"><?php echo htmlspecialchars($invoice['address'] ?? '-'); ?></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 mb-1">电话</div>
                                <div class="text-gray-900"><?php echo htmlspecialchars($invoice['phone'] ?? '-'); ?></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 mb-1">开户银行</div>
                                <div class="text-gray-900"><?php echo htmlspecialchars($invoice['bank_name'] ?? '-'); ?></div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500 mb-1">银行账号</div>
                                <div class="font-mono text-gray-900"><?php echo htmlspecialchars($invoice['bank_account'] ?? '-'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 text-sm font-medium text-gray-600">商品或服务名称</th>
                            <th class="text-right py-3 text-sm font-medium text-gray-600">金额</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b border-gray-100">
                            <td class="py-4 text-gray-900"><?php echo htmlspecialchars($invoice['product_name']); ?></td>
                            <td class="py-4 text-right font-medium text-gray-900">¥<?php echo number_format($invoice['amount'], 2); ?></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="py-4 text-right font-semibold text-gray-700">价税合计</td>
                            <td class="py-4 text-right text-xl font-bold text-blue-600">¥<?php echo number_format($invoice['amount'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="grid grid-cols-2 gap-6">
                <div>
                    <div class="text-sm text-gray-500 mb-1">发票类型</div>
                    <div>
                        <?php 
                            $typeMap = ['electronic' => '电子普通发票', 'general' => '增值税普通发票（纸质）', 'special' => '增值税专用发票（纸质）'];
                            echo $typeMap[$invoice['invoice_type']] ?? $invoice['invoice_type'];
                        ?>
                    </div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">开票人</div>
                    <div class="text-gray-900"><?php echo htmlspecialchars($invoice['issuer_name'] ?? '-'); ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">关联订单号</div>
                    <div class="font-mono text-gray-900"><?php echo htmlspecialchars($invoice['order_no']); ?></div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 mb-1">开票时间</div>
                    <div class="text-gray-900"><?php echo htmlspecialchars($invoice['issued_at']); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">发票操作</h3>
            <div class="space-y-3">
                <button class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-all">
                    📧 重新发送邮件
                </button>
                <button class="w-full px-4 py-3 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition-all">
                    📄 下载 PDF
                </button>
            </div>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-xl p-6">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-green-800">数据完整性说明</h4>
                    <p class="text-sm text-green-700 mt-1">本发票数据为开具时的快照，不受后续发票资料变更影响，保证历史数据不可篡改。</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
