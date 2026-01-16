<?php
require_once 'connect/db.php';


/* ============================
   Monthly Sales (Current Year)
============================ */
$monthlySales = [];
$monthlyTarget = [];

$monthlyQuery = $conn->query("
    SELECT 
        MONTH(invoice_date) AS month,
        IFNULL(SUM(total), 0) AS total
    FROM invoices
    WHERE payment_status = 'paid'
      AND YEAR(invoice_date) = YEAR(CURRENT_DATE())
    GROUP BY MONTH(invoice_date)
");

for ($i = 1; $i <= 12; $i++) {
    $monthlySales[$i] = 0;
    $monthlyTarget[$i] = 200000; // example monthly target
}

while ($row = $monthlyQuery->fetch_assoc()) {
    $monthlySales[(int)$row['month']] = (float)$row['total'];
}

/* ============================
   Quarterly Sales
============================ */
$quarterlySales = [1=>0,2=>0,3=>0,4=>0];
$quarterlyTarget = [1=>600000,2=>600000,3=>600000,4=>600000];

$quarterQuery = $conn->query("
    SELECT 
        QUARTER(invoice_date) AS quarter,
        SUM(total) AS total
    FROM invoices
    WHERE payment_status = 'paid'
      AND YEAR(invoice_date) = YEAR(CURRENT_DATE())
    GROUP BY QUARTER(invoice_date)
");

while ($row = $quarterQuery->fetch_assoc()) {
    $quarterlySales[(int)$row['quarter']] = (float)$row['total'];
}

/* ============================
   Yearly Sales (Last 5 Years)
============================ */
$yearlySales = [];
$yearlyTarget = [];

$yearQuery = $conn->query("
    SELECT 
        YEAR(invoice_date) AS year,
        SUM(total) AS total
    FROM invoices
    WHERE payment_status = 'paid'
    GROUP BY YEAR(invoice_date)
    ORDER BY year DESC
    LIMIT 5
");

while ($row = $yearQuery->fetch_assoc()) {
    $yearlySales[] = [
        'year' => $row['year'],
        'total' => (float)$row['total']
    ];
    $yearlyTarget[] = 2400000;
}
