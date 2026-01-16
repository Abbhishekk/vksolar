<?php
require_once 'connect/db.php';


/* ============================
   Customers Count
============================ */
$customersQuery = $conn->query("
    SELECT COUNT(*) AS total_customers 
    FROM clients
");
$totalCustomers = $customersQuery->fetch_assoc()['total_customers'] ?? 0;

/* ============================
   Orders Count
============================ */
$ordersQuery = $conn->query("
    SELECT COUNT(*) AS total_orders 
    FROM invoices
");
$totalOrders = $ordersQuery->fetch_assoc()['total_orders'] ?? 0;

/* ============================
   Monthly Sales
============================ */
/* ============================
   Monthly Sales TOTAL (Number)
============================ */
$monthlySalesTotalQuery = $conn->query("
    SELECT IFNULL(SUM(total), 0) AS total
    FROM invoices
    WHERE payment_status = 'paid'
      AND MONTH(invoice_date) = MONTH(CURRENT_DATE())
      AND YEAR(invoice_date) = YEAR(CURRENT_DATE())
");
$monthlySalesTotal = (float)($monthlySalesTotalQuery->fetch_assoc()['total'] ?? 0);

/* ============================
   Monthly Target (Number)
============================ */
$monthlyTargetTotal = 200000; // ₹2,00,000 example target

/* ============================
   Target Percentage (Number)
============================ */
$targetPercentage = ($monthlyTargetTotal > 0)
    ? round(($monthlySalesTotal / $monthlyTargetTotal) * 100, 2)
    : 0;

/* ============================
   Recent Orders (Last 5)
============================ */
$recentOrders = [];

$recentOrdersQuery = $conn->query("
    SELECT 
        i.id,
        i.invoice_no,
        i.total,
        i.status,
        i.payment_status,
        i.created_at
    FROM invoices i
    ORDER BY i.created_at DESC
    LIMIT 5
");

while ($row = $recentOrdersQuery->fetch_assoc()) {
    $recentOrders[] = $row;
}
