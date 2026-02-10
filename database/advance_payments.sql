-- Add advance_payments table for receipt voucher system
CREATE TABLE `advance_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_mode` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `remarks` text,
  `receipt_number` varchar(50) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `advance_payments_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insert sample data
INSERT INTO `advance_payments` (`client_id`, `amount`, `payment_mode`, `payment_date`, `remarks`, `receipt_number`) VALUES
(1, 50000.00, 'Cash', '2024-01-15', 'Initial advance for solar installation', 'RV20240115001'),
(1, 25000.00, 'UPI', '2024-01-20', 'Second advance payment', 'RV20240120002');