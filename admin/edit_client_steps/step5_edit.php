<div class="card">
    <div class="card-header">
        <h5 class="card-title">Step 5: Name Change Require</h5>
    </div>
    <div class="card-body">
        <form id="step5Form">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name Change Required?</label>
                    <select class="form-select" name="name_change_require" id="nameChangeRequire">
                        <option value="no" <?php echo ($client_data['name_change_require'] ?? 'no') == 'no' ? 'selected' : ''; ?>>No</option>
                        <option value="yes" <?php echo ($client_data['name_change_require'] ?? 'no') == 'yes' ? 'selected' : ''; ?>>Yes</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="nameChangeApplicationField" 
                     style="display: <?php echo ($client_data['name_change_require'] ?? 'no') == 'yes' ? 'block' : 'none'; ?>;">
                    <label class="form-label">Application Number</label>
                    <input type="text" class="form-control" name="application_no_name_change" 
                           value="<?php echo htmlspecialchars($client_data['application_no_name_change'] ?? ''); ?>">
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('nameChangeRequire').addEventListener('change', function() {
    const applicationField = document.getElementById('nameChangeApplicationField');
    applicationField.style.display = this.value === 'yes' ? 'block' : 'none';
});

function validateStep5() {
    const nameChangeRequire = document.querySelector('select[name="name_change_require"]').value;
    
    if(!nameChangeRequire) {
        alert('Please select whether name change is required');
        return false;
    }
    
    if(nameChangeRequire === 'yes') {
        const applicationNo = document.querySelector('input[name="application_no_name_change"]').value.trim();
        if(!applicationNo) {
            alert('Please enter application number for name change');
            return false;
        }
    }
    
    return true;
}
</script>