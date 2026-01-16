<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 6: PM Suryaghar Portal Registration</h5>
    </div>
    <div class="card-body">
        <form id="step6Form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">PM Suryaghar Registration? <span class="text-danger">*</span></label>
                    <select class="form-select" name="pm_suryaghar_registration" id="pmRegistration" required>
                        <option value="no" <?php echo ($client_data['pm_suryaghar_registration'] ?? 'no') == 'no' ? 'selected' : ''; ?>>No</option>
                        <option value="yes" <?php echo ($client_data['pm_suryaghar_registration'] ?? 'no') == 'yes' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="pmAppIdField" 
                     style="display: <?php echo ($client_data['pm_suryaghar_registration'] ?? 'no') == 'yes' ? 'block' : 'none'; ?>;">
                    <label class="form-label">PM Suryaghar Application ID <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="pm_suryaghar_app_id" 
                           value="<?php echo htmlspecialchars($client_data['pm_suryaghar_app_id'] ?? ''); ?>"
                           <?php echo ($client_data['pm_suryaghar_registration'] ?? 'no') == 'yes' ? 'required' : ''; ?>>
                </div>
                <div class="col-md-6 mb-3" id="pmRegDateField" 
                     style="display: <?php echo ($client_data['pm_suryaghar_registration'] ?? 'no') == 'yes' ? 'block' : 'none'; ?>;">
                    <label class="form-label">Registration Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" name="pm_registration_date" 
                           value="<?php echo htmlspecialchars($client_data['pm_registration_date'] ?? ''); ?>"
                           <?php echo ($client_data['pm_suryaghar_registration'] ?? 'no') == 'yes' ? 'required' : ''; ?>>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('pmRegistration').addEventListener('change', function() {
    const appIdField = document.getElementById('pmAppIdField');
    const regDateField = document.getElementById('pmRegDateField');
    const appIdInput = document.querySelector('input[name="pm_suryaghar_app_id"]');
    const regDateInput = document.querySelector('input[name="pm_registration_date"]');
    
    if (this.value === 'yes') {
        appIdField.style.display = 'block';
        regDateField.style.display = 'block';
        appIdInput.required = true;
        regDateInput.required = true;
    } else {
        appIdField.style.display = 'none';
        regDateField.style.display = 'none';
        appIdInput.required = false;
        regDateInput.required = false;
        appIdInput.value = '';
        regDateInput.value = '';
    }
});

function validateStep6() {
    const pmRegistration = document.getElementById('pmRegistration').value;
    const appId = document.querySelector('input[name="pm_suryaghar_app_id"]').value.trim();
    const regDate = document.querySelector('input[name="pm_registration_date"]').value.trim();
    
    if(pmRegistration === 'yes') {
        if(!appId) {
            alert('Please enter PM Suryaghar Application ID');
            return false;
        }
        if(!regDate) {
            alert('Please select registration date');
            return false;
        }
    }
    
    return true;
}
</script>