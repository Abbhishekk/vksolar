<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 2: Communication & Address Details</h5>
    </div>
    <div class="card-body">
        <form id="step2Form" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">District</label>
                    <input type="text" class="form-control" name="district" 
                           value="<?php echo htmlspecialchars($client_data['district'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Block</label>
                    <input type="text" class="form-control" name="block" 
                           value="<?php echo htmlspecialchars($client_data['block'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Taluka</label>
                    <input type="text" class="form-control" name="taluka" 
                           value="<?php echo htmlspecialchars($client_data['taluka'] ?? ''); ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Village</label>
                    <input type="text" class="form-control" name="village" 
                           value="<?php echo htmlspecialchars($client_data['village'] ?? ''); ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer Mobile Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" name="mobile" 
                           value="<?php echo htmlspecialchars($client_data['mobile'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" 
                           value="<?php echo htmlspecialchars($client_data['email'] ?? ''); ?>">
                </div>
            </div>            
            <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">ADHAR NUMBER</label>
                  <input type="text" name="adhar" class="form-control" maxlength="12" value="<?= htmlspecialchars($client_data['adhar'] ?? '') ?>" placeholder="Enter adhar number">
                  <small class="form-text text-muted">Enter a 12-digit adhar number.</small>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Pincode</label>
                  <input type="text" name="pincode" class="form-control" maxlength="6" value="<?= htmlspecialchars($client_data['pincode'] ?? '') ?>" placeholder="Enter pincode">
                  <small class="form-text text-muted">Enter a 6-digit pincode.</small>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep2() {
    const mobile = document.querySelector('input[name="mobile"]').value.trim();
    
    if(!mobile) {
        alert('Please enter mobile number');
        return false;
    }
    
    if(mobile.length < 10) {
        alert('Please enter a valid mobile number (at least 10 digits)');
        return false;
    }
    
    return true;
}
</script>