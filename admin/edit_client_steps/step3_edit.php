<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 3: MAHADISCOM Email & Mobile Update</h5>
    </div>
    <div class="card-body">
        <form id="step3Form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="mahadiscom_email" 
                           value="<?php echo htmlspecialchars($client_data['mahadiscom_email'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Password <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="mahadiscom_email_password" 
                           value="<?php echo htmlspecialchars($client_data['mahadiscom_email_password'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mobile <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="mahadiscom_mobile" 
                           value="<?php echo htmlspecialchars($client_data['mahadiscom_mobile'] ?? ''); ?>" required>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validateStep3() {
    const email = document.querySelector('input[name="mahadiscom_email"]').value.trim();
    const emailPassword = document.querySelector('input[name="mahadiscom_email_password"]').value.trim();
    const mobile = document.querySelector('input[name="mahadiscom_mobile"]').value.trim();
    
    if(!email) {
        alert('Please enter MAHADISCOM email');
        return false;
    }
    
    if(!emailPassword) {
        alert('Please enter email password');
        return false;
    }
    
    if(!mobile) {
        alert('Please enter MAHADISCOM mobile');
        return false;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailRegex.test(email)) {
        alert('Please enter a valid email address');
        return false;
    }
    
    // Validate mobile number (10 digits)
    const mobileRegex = /^\d{10}$/;
    if(!mobileRegex.test(mobile.replace(/\D/g, ''))) {
        alert('Please enter a valid 10-digit mobile number');
        return false;
    }
    
    return true;
}
</script>