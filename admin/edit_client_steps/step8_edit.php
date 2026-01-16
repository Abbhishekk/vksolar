<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 8: Bank Loan Details</h5>
    </div>
    <div class="card-body">
        <form id="step8Form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank Loan Required? <span class="text-danger">*</span></label>
                    <select class="form-select" name="bank_loan_status" id="bankLoanStatus" required onchange="toggleBankLoanFields()">
                        <option value="">-- Select --</option>
                        <option value="no" <?php echo ($client_data['bank_loan_status'] ?? '') == 'no' ? 'selected' : ''; ?>>No</option>
                        <option value="yes" <?php echo ($client_data['bank_loan_status'] ?? '') == 'yes' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
            </div>
            
            <!-- Bank Loan Fields -->
            <div id="bankLoanFields" style="display: <?php echo ($client_data['bank_loan_status'] ?? '') == 'yes' ? 'block' : 'none'; ?>;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bank_name" 
                               value="<?php echo htmlspecialchars($client_data['bank_name'] ?? ''); ?>"
                               <?php echo ($client_data['bank_loan_status'] ?? '') == 'yes' ? 'required' : ''; ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="account_number" 
                               value="<?php echo htmlspecialchars($client_data['account_number'] ?? ''); ?>"
                               <?php echo ($client_data['bank_loan_status'] ?? '') == 'yes' ? 'required' : ''; ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ifsc_code" 
                               value="<?php echo htmlspecialchars($client_data['ifsc_code'] ?? ''); ?>"
                               <?php echo ($client_data['bank_loan_status'] ?? '') == 'yes' ? 'required' : ''; ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jan Samartha Application No</label>
                        <input type="text" class="form-control" name="jan_samartha_application_no" 
                               value="<?php echo htmlspecialchars($client_data['jan_samartha_application_no'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Loan Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="loan_amount" 
                               value="<?php echo htmlspecialchars($client_data['loan_amount'] ?? ''); ?>"
                               <?php echo ($client_data['bank_loan_status'] ?? '') == 'yes' ? 'required' : ''; ?>>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">First Installment Amount (₹)</label>
                        <input type="number" class="form-control" name="first_installment_amount" 
                               value="<?php echo htmlspecialchars($client_data['first_installment_amount'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Second Installment Amount (₹)</label>
                        <input type="number" class="form-control" name="second_installment_amount" 
                               value="<?php echo htmlspecialchars($client_data['second_installment_amount'] ?? ''); ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Remaining Amount (₹)</label>
                        <input type="number" class="form-control" name="remaining_amount" 
                               value="<?php echo htmlspecialchars($client_data['remaining_amount'] ?? ''); ?>">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleBankLoanFields() {
    const bankLoanStatus = document.getElementById('bankLoanStatus').value;
    const bankLoanFields = document.getElementById('bankLoanFields');
    const requiredFields = bankLoanFields.querySelectorAll('[required]');
    
    if (bankLoanStatus === 'yes') {
        bankLoanFields.style.display = 'block';
        // Make fields required
        requiredFields.forEach(field => {
            field.required = true;
        });
    } else {
        bankLoanFields.style.display = 'none';
        // Remove required attribute and clear values
        requiredFields.forEach(field => {
            field.required = false;
            field.value = '';
        });
        
        // Clear all bank loan fields
        const allFields = bankLoanFields.querySelectorAll('input');
        allFields.forEach(field => {
            field.value = '';
        });
    }
}

function validateStep8() {
    const bankLoanStatus = document.getElementById('bankLoanStatus').value;
    
    if (!bankLoanStatus) {
        alert('Please select whether bank loan is required');
        return false;
    }
    
    if (bankLoanStatus === 'yes') {
        const bankName = document.querySelector('input[name="bank_name"]').value.trim();
        const accountNumber = document.querySelector('input[name="account_number"]').value.trim();
        const ifscCode = document.querySelector('input[name="ifsc_code"]').value.trim();
        const loanAmount = document.querySelector('input[name="loan_amount"]').value.trim();
        
        if (!bankName) {
            alert('Please enter bank name');
            return false;
        }
        
        if (!accountNumber) {
            alert('Please enter account number');
            return false;
        }
        
        if (!ifscCode) {
            alert('Please enter IFSC code');
            return false;
        }
        
        if (!loanAmount || parseFloat(loanAmount) <= 0) {
            alert('Please enter valid loan amount');
            return false;
        }
        
        // Validate IFSC code format (basic validation)
        const ifscRegex = /^[A-Z]{4}0[A-Z0-9]{6}$/;
        if (!ifscRegex.test(ifscCode.toUpperCase())) {
            alert('Please enter a valid IFSC code format (e.g., SBIN0000123)');
            return false;
        }
    }
    
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const bankLoanStatus = document.getElementById('bankLoanStatus');
    if (bankLoanStatus.value === 'yes') {
        toggleBankLoanFields();
    }
});
</script>