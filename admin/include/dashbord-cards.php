<div class="col-12">
            <div class="dashboard">
              <div class="card card-stat">
                <div class="card-header">
                  <div class="card-icon">
                    <i class="bx bx-group "></i>
                  </div>
                </div>
                <div class="card-body">
                  <p class="card-title">Customers</p>
                  <h3 class="card-value"><?= number_format($totalCustomers) ?></h3>

                </div>
                <div class="card-footer">
                  <span class="change-indicator positive">
                    <i class="bx bx-up-arrow-alt"></i> 11.01%
                  </span>
                </div>
              </div>

              <div class="card card-stat">
                <div class="card-header ">
                  <div class="card-icon ">
                    <i class="bx bx-cube"></i>
                  </div>
                </div>
                <div class="card-body">
                  <p class="card-title">Orders</p>
                  <h3 class="card-value"><?= number_format($totalOrders) ?></h3>

                </div>
                <div class="card-footer">
                  <span class="change-indicator negative">
                    
                  </span>
                </div>
              </div>

              <div class="card">
                <div class="card-header">
                  <h4 class="card-title-main text-white">Monthly Sales</h4>
                  <div class="card-options">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                  </div>
                </div>
                <div class="card-body">
                  <div id="sales-chart" style="height: 250px"></div>
                </div>
              </div>

              <div class="card card-target">
                <div class="card-header">
                  <div>
                    <h4 class="card-title-main text-white">Monthly Target</h4>
                    <p class="card-subtitle text-white">
                      Target you've set for each month
                    </p>
                  </div>
                  <div class="card-options">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                  </div>
                </div>
                <div class="card-body">
                  <div id="target-chart" style="height: 250px"></div>
                  <div class="target-info">
                    
                  </div>
                </div>
                <div class="card-footer target-summary">
                  <div class="summary-item">
  <p>Target</p>
  <span class="negative">₹<?= number_format($monthlyTargetTotal) ?></span>
</div>

<div class="summary-item">
  <p>Revenue</p>
  <span class="positive">₹<?= number_format($monthlySalesTotal) ?></span>
</div>

<div class="summary-item">
  <p>Remaining</p>
  <span class="negative">
    ₹<?= number_format($monthlyTargetTotal - $monthlySalesTotal) ?>
  </span>
</div>


                </div>
              </div>
            </div>
          </div>