<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">发票资料维护</h1>
    <p class="text-gray-600">维护您的企业发票信息，系统将在开票时使用这些信息。</p>
</div>

<div class="bg-white rounded-xl shadow-lg p-8 max-w-3xl">
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-blue-800">重要提示</h3>
                <p class="text-sm text-blue-700 mt-1">发票信息变更后，仅影响新的开票申请。已开出的历史发票数据为快照，不会被修改。</p>
            </div>
        </div>
    </div>

    <form action="/invoice/info" method="POST" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label for="company_name" class="block text-sm font-medium text-gray-700 mb-2">
                    发票抬头 <span class="text-red-500">*</span>
                </label>
                <input type="text" id="company_name" name="company_name" 
                       value="<?php echo htmlspecialchars($invoiceInfo['company_name'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="请输入公司全称" required>
            </div>

            <div class="md:col-span-2">
                <label for="tax_id" class="block text-sm font-medium text-gray-700 mb-2">
                    纳税人识别号（税号） <span class="text-red-500">*</span>
                </label>
                <input type="text" id="tax_id" name="tax_id" 
                       value="<?php echo htmlspecialchars($invoiceInfo['tax_id'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="请输入15-20位纳税人识别号" required>
            </div>

            <div class="md:col-span-2">
                <label for="invoice_email" class="block text-sm font-medium text-gray-700 mb-2">
                    开票邮箱 <span class="text-red-500">*</span>
                </label>
                <input type="email" id="invoice_email" name="invoice_email" 
                       value="<?php echo htmlspecialchars($invoiceInfo['invoice_email'] ?? ''); ?>"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       placeholder="用于接收电子发票的邮箱" required>
            </div>

            <div class="md:col-span-2">
                <label for="invoice_preference" class="block text-sm font-medium text-gray-700 mb-2">
                    开票偏好 <span class="text-red-500">*</span>
                </label>
                <select id="invoice_preference" name="invoice_preference" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        onchange="toggleSpecialInvoiceFields()">
                    <option value="electronic" <?php echo ($invoiceInfo['invoice_preference'] ?? 'electronic') === 'electronic' ? 'selected' : ''; ?>>电子普通发票</option>
                    <option value="general" <?php echo ($invoiceInfo['invoice_preference'] ?? '') === 'general' ? 'selected' : ''; ?>>增值税普通发票（纸质）</option>
                    <option value="special" <?php echo ($invoiceInfo['invoice_preference'] ?? '') === 'special' ? 'selected' : ''; ?>>增值税专用发票（纸质）</option>
                </select>
                <p class="text-sm text-gray-500 mt-1">选择专用发票需填写完整的公司信息。</p>
            </div>
        </div>

        <div id="special_invoice_fields" class="space-y-6 border-t pt-6 <?php echo ($invoiceInfo['invoice_preference'] ?? 'electronic') !== 'special' ? 'hidden' : ''; ?>">
            <h3 class="text-lg font-semibold text-gray-800">专用发票附加信息</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                        公司地址 <span class="text-red-500" id="addr_required">*</span>
                    </label>
                    <input type="text" id="address" name="address" 
                           value="<?php echo htmlspecialchars($invoiceInfo['address'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="请输入公司注册地址">
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        公司电话 <span class="text-red-500" id="phone_required">*</span>
                    </label>
                    <input type="text" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($invoiceInfo['phone'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="请输入公司联系电话">
                </div>

                <div class="md:col-span-2">
                    <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">
                        开户银行 <span class="text-red-500" id="bank_required">*</span>
                    </label>
                    <input type="text" id="bank_name" name="bank_name" 
                           value="<?php echo htmlspecialchars($invoiceInfo['bank_name'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="请输入开户银行全称">
                </div>

                <div class="md:col-span-2">
                    <label for="bank_account" class="block text-sm font-medium text-gray-700 mb-2">
                        银行账号 <span class="text-red-500" id="account_required">*</span>
                    </label>
                    <input type="text" id="bank_account" name="bank_account" 
                           value="<?php echo htmlspecialchars($invoiceInfo['bank_account'] ?? ''); ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                           placeholder="请输入银行账号">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4 pt-6">
            <button type="submit" 
                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-lg hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                保存发票资料
            </button>
        </div>
    </form>
</div>

<script>
function toggleSpecialInvoiceFields() {
    const preference = document.getElementById('invoice_preference').value;
    const specialFields = document.getElementById('special_invoice_fields');
    const requiredMarks = ['addr_required', 'phone_required', 'bank_required', 'account_required'];
    
    if (preference === 'special') {
        specialFields.classList.remove('hidden');
        document.getElementById('address').required = true;
        document.getElementById('phone').required = true;
        document.getElementById('bank_name').required = true;
        document.getElementById('bank_account').required = true;
    } else {
        specialFields.classList.add('hidden');
        document.getElementById('address').required = false;
        document.getElementById('phone').required = false;
        document.getElementById('bank_name').required = false;
        document.getElementById('bank_account').required = false;
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
