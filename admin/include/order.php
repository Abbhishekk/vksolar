 <div class="col-12">
            <div class="card mt-4">
              <div class="card-body recent-orders">
                <h5 class="card-title fw-bold mb-3">Recent Orders</h5>
                <table class="table table-borderless table-hover">
                  <thead>
                    <tr>
                      <th>Products</th>
                      <th>Category</th>
                      <th>Price</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
<?php if (!empty($recentOrders)): ?>
  <?php foreach ($recentOrders as $order): ?>
    <tr>
      <td>
        <div class="d-flex align-items-center">
          <img
            src="https://placehold.co/40x40/2E8B57/FFFFFF?text=INV"
            alt="Invoice"
          />
          <div class="ms-3">
            <h6 class="mb-0 fw-semibold">
              Invoice #<?= htmlspecialchars($order['invoice_no']) ?>
            </h6>
            <small class="text-muted">
              <?= date('d M Y', strtotime($order['created_at'])) ?>
            </small>
          </div>
        </div>
      </td>

      <td>Invoice</td>

      <td class="fw-semibold">
        ₹<?= number_format((float)$order['total'], 2) ?>
      </td>

      <td>
        <?php
          $statusClass = match ($order['status']) {
            'final' => 'status-delivered',
            'draft' => 'status-pending',
            'cancelled' => 'status-canceled',
            default => 'status-pending'
          };
        ?>
        <span class="status-badge <?= $statusClass ?>">
          <?= ucfirst($order['status']) ?>
        </span>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td colspan="4" class="text-center text-muted">
      No recent orders found
    </td>
  </tr>
<?php endif; ?>
</tbody>

                </table>
              </div>
            </div>
          </div>