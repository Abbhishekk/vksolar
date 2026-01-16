<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 4: MAHADISCOM Registration</h5>
    </div>
    <div class="card-body">
        <form id="step4Form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">User ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="mahadiscom_user_id" 
                           value="<?php echo htmlspecialchars($client_data['mahadiscom_user_id'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" name="mahadiscom_password" 
                           value="<?php echo htmlspecialchars($client_data['mahadiscom_password'] ?? ''); ?>" required>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep4() {
    const userId = document.querySelector('input[name="mahadiscom_user_id"]').value.trim();
    const password = document.querySelector('input[name="mahadiscom_password"]').value.trim();
    
    if(!userId) {
        alert('Please enter MAHADISCOM User ID');
        return false;
    }
    
    if(!password) {
        alert('Please enter MAHADISCOM password');
        return false;
    }
    
    return true;
}
</script>