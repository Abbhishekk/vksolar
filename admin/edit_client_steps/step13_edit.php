<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 13: PM Suryaghar Redeem Status</h5>
    </div>
    <div class="card-body">
        <form id="step13Form" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">PM Suryaghar Subsidy Redeemed? <span class="text-danger">*</span></label>
                    <select class="form-select" name="pm_redeem_status" id="pmRedeemStatus" required onchange="toggleSubsidyFields()">
                        <option value="">-- Select --</option>
                        <option value="no" <?php echo ($client_data['pm_redeem_status'] ?? '') == 'no' ? 'selected' : ''; ?>>No</option>
                        <option value="yes" <?php echo ($client_data['pm_redeem_status'] ?? '') == 'yes' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
            </div>
            
            <!-- Subsidy Details (Hidden by default) -->
            <div id="subsidyDetails" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subsidy Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="subsidy_amount" 
                               value="<?php echo htmlspecialchars($client_data['subsidy_amount'] ?? ''); ?>" step="0.01" min="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subsidy Redeem Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="subsidy_redeem_date" 
                               value="<?php echo htmlspecialchars($client_data['subsidy_redeem_date'] ?? ''); ?>">
                    </div>
                    
                    <!-- Subsidy Document Upload -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Subsidy Redeem Proof Document</label>
                        <input type="file" class="form-control" name="subsidy_redeem_document" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF, JPG, PNG (Max 5MB)</small>
                        
                        <!-- File Preview -->
                        <?php 
                        $hasSubsidyDoc = hasClientDocument($connect->dbconnect(), $client_id ?? 0, 'subsidy_redeem');
                        $subsidyDocPath = getClientDocumentPath($connect->dbconnect(), $client_id ?? 0, 'subsidy_redeem');
                        $subsidyDocUrl = getDocumentWebUrl($subsidyDocPath);
                        $isSubsidyImage = $subsidyDocPath && (strpos($subsidyDocPath, '.jpg') !== false || strpos($subsidyDocPath, '.jpeg') !== false || strpos($subsidyDocPath, '.png') !== false);
                        ?>
                        <?php if($hasSubsidyDoc): ?>
                        <div class="mt-2">
                            <small class="text-muted">Current Document:</small>
                            <div class="d-flex align-items-center mt-1">
                                <?php if($isSubsidyImage && $subsidyDocUrl): ?>
                                    <img src="<?php echo $subsidyDocUrl; ?>" alt="Subsidy Redeem Proof" 
                                         class="me-2" style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center me-2" 
                                         style="width: 60px; height: 60px; border-radius: 5px;">
                                        <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 24px;"></i>
                                    </div>
                                <?php endif; ?>
                                <a href="<?php echo $subsidyDocUrl ?: '#'; ?>" target="_blank" 
                                   class="btn btn-sm btn-outline-primary">View Document</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSubsidyFields() {
    const pmRedeemStatus = document.getElementById('pmRedeemStatus').value;
    const subsidyDetails = document.getElementById('subsidyDetails');
    const subsidyAmount = document.querySelector('input[name="subsidy_amount"]');
    const subsidyDate = document.querySelector('input[name="subsidy_redeem_date"]');
    
    if (pmRedeemStatus === 'yes') {
        subsidyDetails.style.display = 'block';
        if (subsidyAmount) subsidyAmount.required = true;
        if (subsidyDate) subsidyDate.required = true;
    } else {
        subsidyDetails.style.display = 'none';
        if (subsidyAmount) {
            subsidyAmount.required = false;
            subsidyAmount.value = '';
        }
        if (subsidyDate) {
            subsidyDate.required = false;
            subsidyDate.value = '';
        }
    }
}

function validateStep13() {
    const pmRedeemStatus = document.getElementById('pmRedeemStatus').value;
    
    if (!pmRedeemStatus) {
        alert('Please select PM Suryaghar Redeem Status');
        return false;
    }
    
    if (pmRedeemStatus === 'yes') {
        const subsidyAmount = document.querySelector('input[name="subsidy_amount"]').value.trim();
        const subsidyDate = document.querySelector('input[name="subsidy_redeem_date"]').value.trim();
        
        if (!subsidyAmount || parseFloat(subsidyAmount) <= 0) {
            alert('Please enter valid subsidy amount');
            document.querySelector('input[name="subsidy_amount"]').focus();
            return false;
        }
        
        if (!subsidyDate) {
            alert('Please select subsidy redeem date');
            document.querySelector('input[name="subsidy_redeem_date"]').focus();
            return false;
        }
    }
    
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const pmRedeemStatus = document.getElementById('pmRedeemStatus');
    if (pmRedeemStatus.value === 'yes') {
        toggleSubsidyFields();
    }
});
</script>