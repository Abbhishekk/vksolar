<h4>Step 15: Reference Details</h4>
<form id="step15Form" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Reference Name</label>
        <input type="text" class="form-control" name="reference_name" value="<?php echo htmlspecialchars($client_data['reference_name'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Reference Contact</label>
        <input type="text" class="form-control" name="reference_contact" value="<?php echo htmlspecialchars($client_data['reference_contact'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Commission Amount (₹)</label>
        <input type="number" step="0.01" class="form-control" name="commission_amount" value="<?php echo htmlspecialchars($client_data['commission_amount'] ?? ''); ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Commission Paid Date</label>
        <input type="date" class="form-control" name="commission_paid_date" value="<?php echo htmlspecialchars($client_data['commission_paid_date'] ?? ''); ?>">
    </div>
</form>