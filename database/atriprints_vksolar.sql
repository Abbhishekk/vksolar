-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 16, 2026 at 09:58 AM
-- Server version: 8.0.44-cll-lve
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `atriprints_vksolar`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank_quotations`
--

CREATE TABLE `bank_quotations` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `quotation_number` varchar(100) NOT NULL,
  `quotation_date` date NOT NULL,
  `validity_date` date NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_address` text,
  `pin_code` varchar(10) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `project_location` varchar(255) DEFAULT NULL,
  `plant_capacity` varchar(50) DEFAULT NULL,
  `system_type` varchar(50) DEFAULT NULL,
  `estimated_generation` varchar(100) DEFAULT NULL,
  `system_description` text,
  `bank_id` int NOT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `subsidy` decimal(12,2) DEFAULT NULL,
  `final_amount` decimal(12,2) DEFAULT NULL,
  `status` enum('draft','final') DEFAULT 'draft',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `bank_quotations`
--

INSERT INTO `bank_quotations` (`id`, `client_id`, `quotation_number`, `quotation_date`, `validity_date`, `customer_name`, `customer_address`, `pin_code`, `customer_phone`, `customer_email`, `project_location`, `plant_capacity`, `system_type`, `estimated_generation`, `system_description`, `bank_id`, `total_amount`, `subsidy`, `final_amount`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, '01', '2026-01-10', '2026-04-10', 'SHRI MANOHAR TEMBHURKAR', 'TEACHER COLONY WARD NO. 07 TALODHI NAGBHID CHANDRAPUR', '999999', '9822121061', 'temburkaranshul.maddy@gmail.com', 'TEACHER COLONY WARD NO. 07 TALODHI NAGBHID CHANDRAPUR', '3 kWp', 'On Grid', '45000', '0', 2, 200000.00, 78000.00, 122000.00, 'draft', 2, '2026-01-10 06:22:09', '2026-01-10 06:27:38'),
(2, 1, '01', '2026-01-10', '2026-04-10', 'SHRI MANOHAR TEMBHURKAR', 'TEACHER COLONY WARD NO. 07 TALODHI NAGBHID CHANDRAPUR', '999999', '9822121061', 'temburkaranshul.maddy@gmail.com', 'TEACHER COLONY WARD NO. 07 TALODHI NAGBHID CHANDRAPUR', '3 kWp', 'On Grid', '45000', 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system, complete with solar panels, 3kW inverter, all other accessories, net meter and connecting cables, including elevated standard structures.', 2, 200000.00, 78000.00, 122000.00, 'draft', 2, '2026-01-10 06:24:50', NULL),
(3, 6, '2', '2026-01-10', '2026-04-10', 'NANDA NARAYAN LAYSE', 'p no 29 pathan layout parsodi nagpur 440022', '440022', '7972740850', 'shaileshlayase@gmail.com', 'p no 29 pathan layout parsodi nagpur 440022', ' 3 kWp', 'On Grid', '4500', 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system, complete with solar panels, 3kW inverter, all other accessories, net meter and connecting cables, including elevated standard structures.', 2, 200000.00, 78000.00, 122000.00, 'draft', 2, '2026-01-10 09:56:43', NULL),
(4, 6, '2', '2026-01-10', '2026-04-10', 'NANDA NARAYAN LAYSE', 'p no 29 pathan layout parsodi nagpur 440022', '440022', '7972740850', 'shaileshlayase@gmail.com', 'p no 29 pathan layout parsodi nagpur 440022', ' 3 kWp', 'On Grid', '4500', 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system, complete with solar panels, 3kW inverter, all other accessories, net meter and connecting cables, including elevated standard structures.', 2, 200000.00, 78000.00, 122000.00, 'draft', 2, '2026-01-10 10:06:10', NULL),
(5, 7, 'v123', '2026-01-13', '2026-04-13', 'JAYSHREE JIWAN GOURKHEDE', 'pl no 71 govt press layout dhaba nagpur 440016', '440016', '8999466479', 'abhishekgourkhede88@gmail.com', 'pl no 71 govt press layout dhaba nagpur 440016', ' kWp', 'On Grid', '4500', 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system, complete with solar panels, 3kW inverter, all other accessories, net meter and connecting cables, including elevated standard structures.', 2, 1000.00, 60.00, 940.00, 'draft', 2, '2026-01-13 11:34:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bank_quotation_products`
--

CREATE TABLE `bank_quotation_products` (
  `id` int NOT NULL,
  `quotation_id` int NOT NULL,
  `description` text,
  `quantity` int DEFAULT NULL,
  `unit_price` decimal(12,2) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL
) ;

--
-- Dumping data for table `bank_quotation_products`
--

INSERT INTO `bank_quotation_products` (`id`, `quotation_id`, `description`, `quantity`, `unit_price`, `amount`) VALUES
(2, 2, 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system', 1, 200000.00, 200000.00),
(3, 1, 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system', 1, 200000.00, 200000.00),
(4, 3, 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system', 1, 200000.00, 200000.00),
(5, 4, 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system', 1, 200000.00, 200000.00),
(6, 5, 'Sale of solar power generating system including supply, installation, and commissioning of 3kW On-Grid solar PV system', 10, 100.00, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int NOT NULL,
  `name` varchar(255)  NOT NULL,
  `consumer_number` varchar(100)  NOT NULL,
  `billing_unit` varchar(100)  DEFAULT NULL,
  `adhar` bigint NOT NULL,
  `mobile` varchar(15)  DEFAULT NULL,
  `email` varchar(255)  DEFAULT NULL,
  `district` varchar(255)  DEFAULT NULL,
  `block` varchar(255)  DEFAULT NULL,
  `taluka` varchar(255)  DEFAULT NULL,
  `pincode` int NOT NULL,
  `village` varchar(255)  DEFAULT NULL,
  `location` text ,
  `mahadiscom_email` varchar(255)  DEFAULT NULL,
  `mahadiscom_email_password` varchar(255)  DEFAULT NULL,
  `mahadiscom_mobile` varchar(15)  DEFAULT NULL,
  `mahadiscom_user_id` varchar(255)  DEFAULT NULL,
  `mahadiscom_password` varchar(255)  DEFAULT NULL,
  `name_change_require` enum('yes','no')  DEFAULT NULL,
  `application_no_name_change` varchar(255)  DEFAULT NULL,
  `pm_suryaghar_registration` enum('yes','no')  DEFAULT NULL,
  `pm_suryaghar_app_id` varchar(255)  DEFAULT NULL,
  `pm_registration_date` date DEFAULT NULL,
  `load_change_application_number` varchar(255)  DEFAULT NULL,
  `rooftop_solar_application_number` varchar(255)  DEFAULT NULL,
  `kilo_watt` decimal(10,2) DEFAULT NULL,
  `load_change_status` enum('Done','Not')  DEFAULT NULL,
  `bank_loan_status` enum('yes','no')  DEFAULT NULL,
  `bank_name` varchar(255)  DEFAULT NULL,
  `account_number` varchar(255)  DEFAULT NULL,
  `ifsc_code` varchar(255)  DEFAULT NULL,
  `jan_samartha_application_no` varchar(255)  DEFAULT NULL,
  `loan_amount` decimal(15,2) DEFAULT NULL,
  `first_installment_amount` decimal(15,2) DEFAULT NULL,
  `second_installment_amount` decimal(15,2) DEFAULT NULL,
  `remaining_amount` decimal(15,2) DEFAULT NULL,
  `inverter_company_name` varchar(255)  DEFAULT NULL,
  `inverter_capacity` varchar(50)  DEFAULT NULL,
  `inverter_serial_number` varchar(255)  DEFAULT NULL,
  `solar_type` enum('DCR','NON-DCR')  DEFAULT NULL,
  `dcr_certificate_number` varchar(255)  DEFAULT NULL,
  `number_of_panels` int DEFAULT NULL,
  `company_name` varchar(255)  NOT NULL,
  `wattage` int NOT NULL,
  `panel_serial_numbers` text ,
  `rts_portal_status` enum('yes','no')  DEFAULT NULL,
  `meter_number` varchar(255)  DEFAULT NULL,
  `meter_installation_date` date DEFAULT NULL,
  `pm_redeem_status` enum('yes','no')  DEFAULT NULL,
  `subsidy_amount` decimal(15,2) DEFAULT NULL,
  `subsidy_redeem_date` date DEFAULT NULL,
  `reference_name` varchar(255)  DEFAULT NULL,
  `reference_contact` varchar(255)  DEFAULT NULL,
  `estimate_amount` decimal(15,2) DEFAULT NULL,
  `geo_tagging_photo` varchar(500)  DEFAULT NULL,
  `geo_tagged_photo` varchar(500)  DEFAULT NULL,
  `email_password` varchar(255)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `step9_completed` tinyint(1) DEFAULT '0',
  `step12_completed` tinyint(1) DEFAULT '0',
  `step10_completed` tinyint(1) DEFAULT '0'
) ;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `consumer_number`, `billing_unit`, `adhar`, `mobile`, `email`, `district`, `block`, `taluka`, `pincode`, `village`, `location`, `mahadiscom_email`, `mahadiscom_email_password`, `mahadiscom_mobile`, `mahadiscom_user_id`, `mahadiscom_password`, `name_change_require`, `application_no_name_change`, `pm_suryaghar_registration`, `pm_suryaghar_app_id`, `pm_registration_date`, `load_change_application_number`, `rooftop_solar_application_number`, `kilo_watt`, `load_change_status`, `bank_loan_status`, `bank_name`, `account_number`, `ifsc_code`, `jan_samartha_application_no`, `loan_amount`, `first_installment_amount`, `second_installment_amount`, `remaining_amount`, `inverter_company_name`, `inverter_capacity`, `inverter_serial_number`, `solar_type`, `dcr_certificate_number`, `number_of_panels`, `company_name`, `wattage`, `panel_serial_numbers`, `rts_portal_status`, `meter_number`, `meter_installation_date`, `pm_redeem_status`, `subsidy_amount`, `subsidy_redeem_date`, `reference_name`, `reference_contact`, `estimate_amount`, `geo_tagging_photo`, `geo_tagged_photo`, `email_password`, `created_at`, `updated_at`, `step9_completed`, `step12_completed`, `step10_completed`) VALUES
(1, 'SHRI MANOHAR TEMBHURKAR', '463040218726', '4336', 631611994662, '9822121061', 'temburkaranshul.maddy@gmail.com', 'CHANDRAPUR', 'CHANDRAPUR', 'NAGBHID', 999999, 'TEACHER COLONY WARD NO. 07 TALODHI NAGBHID CHANDRAPUR', '', 'officeomsairament2017@gmail.com', '1', '9529750282', '1', '1', 'no', '', 'yes', 'NP-MHSED26-9537253', '2026-01-03', '73770415', '73770415', NULL, NULL, 'no', '', '', '', '', 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, NULL, NULL, 'U5966290', NULL, 'no', NULL, NULL, 'SHAILENDRA SIR', '90283 80699', NULL, NULL, NULL, NULL, '2026-01-06 10:21:56', '2026-01-06 10:44:11', 0, 0, 0),
(2, 'TUKARAM SANTOSH WAGHADE', '412583254605', '3956', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 06:11:44', '2026-01-10 06:11:44', 0, 0, 0),
(4, 'SACHIN VISHWAS THAMKE', '410032705697', '3905', 0, NULL, NULL, NULL, NULL, NULL, 0, NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 06:33:12', '2026-01-10 06:33:12', 0, 0, 0),
(5, 'PRANAY RAVINDRA KAMBLE', '411350027467', '0562', 251677441444, '9370484353', 'pranay18.kamble@gmail.com', 'NAGPUR', 'NAGPUR', 'NAGPUR', 441108, 'h no 46,pl no27 mihan khapri punarvasan 441108', '', 'officeomsairament2017@gmail.com', 'office@12345', '9529750282', 'admin', 'admin123', 'no', '', 'yes', 'NP-MHSED26-9575056', '2026-01-06', '', 'NP-MHSED26-9575056', NULL, NULL, 'yes', 'BANK OF INDIA', '872910510000082', 'BKID0008729', 'ANS-SOLAR-10941572-2398532', 200000.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, NULL, 'no', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 07:54:04', '2026-01-10 09:34:55', 0, 0, 0),
(6, 'NANDA NARAYAN LAYSE', '410015413372', '4683 TRIMURTI NAGAR', 672068887805, '7972740850', 'shaileshlayase@gmail.com', 'NAGPUR', 'NAGPUR', 'NAGPUR', 440022, 'p no 29 pathan layout parsodi nagpur 440022', '', 'officeomsairament2017@gmail.com', '', '9529750282', '12345', '12345', 'no', '', 'no', '', '0000-00-00', NULL, NULL, NULL, NULL, 'yes', 'STATE BANK OF INDIA', '43189472774', 'SBIN0009057', 'ANS-SOLAR-10986854-2424920', 200000.00, 0.00, 0.00, 200000.00, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-10 09:38:51', '2026-01-10 09:51:25', 0, 0, 0),
(7, 'JAYSHREE JIWAN GOURKHEDE', '410017255880', '4690 MIDC-I', 677762356232, '8999466479', 'abhishekgourkhede88@gmail.com', 'NAGPUR', 'NAGPUR', 'NAGPUR', 440016, 'pl no 71 govt press layout dhaba nagpur 440016', '', 'officeomsairament2017@gmail.com', '', '9529750282', '12345', '1', 'no', '', 'yes', 'NP-MHSED25-9416748', '2025-12-23', NULL, NULL, NULL, NULL, 'yes', 'CANARA BANK', '5096101000626', 'CNRB0005096', 'ANS-SOLAR-10703248-2276355', 200000.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, NULL, NULL, NULL, NULL, 'no', NULL, NULL, 'VINAY SIR', '8007955787', NULL, NULL, NULL, NULL, '2026-01-10 11:49:44', '2026-01-10 11:56:01', 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `client_documents`
--

CREATE TABLE `client_documents` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `document_type` enum('aadhar','electric_bill','pan_card','bank_passbook','bank_statement','it_return','other','fitting_photos','pm_documents','rts_document','meter_photo','subsidy_redeem','solar_panel_photo','inverter_photo','geotag_photo','model_agreement','dcr_certificate','salary_slip','gumasta','client_signature')   NOT NULL,
  `file_path` varchar(500)  NOT NULL,
  `file_name` varchar(255)  DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `mime_type` varchar(100)  DEFAULT NULL,
  `original_filename` varchar(255)  DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `commissioning_certificates`
--

CREATE TABLE `commissioning_certificates` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `consumer_name` varchar(255) DEFAULT NULL,
  `consumer_number` varchar(100) DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `installation_address` text,
  `re_arrangement_type` varchar(255) DEFAULT NULL,
  `re_source` varchar(255) DEFAULT NULL,
  `sanctioned_capacity` decimal(10,2) DEFAULT NULL,
  `capacity_type` varchar(100) DEFAULT NULL,
  `project_model` varchar(255) DEFAULT NULL,
  `installation_date` date DEFAULT NULL,
  `leter_no` varchar(20)  NOT NULL,
  `inverter_capacity` decimal(10,2) DEFAULT NULL,
  `inverter_make` varchar(255) DEFAULT NULL,
  `number_of_modules` int DEFAULT NULL,
  `module_capacity` int DEFAULT NULL,
  `module_make` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `rep_name` varchar(255) DEFAULT NULL,
  `company_phone` varchar(50) DEFAULT NULL,
  `company_email` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `company_bank_details`
--

CREATE TABLE `company_bank_details` (
  `id` int NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_type` varchar(50) DEFAULT NULL,
  `ifsc_code` varchar(20) NOT NULL,
  `bank_gst` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `company_bank_details`
--

INSERT INTO `company_bank_details` (`id`, `bank_name`, `branch_name`, `account_number`, `account_type`, `ifsc_code`, `bank_gst`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BOI', 'Godhani', 'bhu1242636633', 'Saving', '1202j3i33i3i393939', '1342627378338', 0, 1, '2026-01-09 14:36:41', '2026-01-10 06:24:15'),
(2, 'STATE BANK OF INDIA', 'WADI', '44488375874', 'CURRENT', 'SBIN0012710', '', 1, 2, '2026-01-10 06:24:06', '2026-01-10 06:25:57');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_address` text,
  `property_type` enum('residential','commercial','industrial') DEFAULT NULL,
  `meter_type` enum('single_phase','three_phase','ct_meter','ip_meter') DEFAULT NULL,
  `source_quotation_id` int DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `declaration_documents`
--

CREATE TABLE `declaration_documents` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `system_capacity` decimal(6,2) NOT NULL,
  `consumer_name` varchar(255) NOT NULL,
  `project_address` text NOT NULL,
  `application_number` varchar(100) NOT NULL,
  `application_date` date NOT NULL,
  `discom_name` varchar(150) NOT NULL,
  `pv_module_capacity` decimal(6,2) NOT NULL,
  `pv_module_count` int NOT NULL,
  `inverter_no` varchar(150) NOT NULL,
  `pv_module_make` varchar(150) NOT NULL,
  `cell_manufacturer` varchar(150) NOT NULL,
  `cell_gst_invoice` varchar(150) NOT NULL,
  `panel_serial_numbers` text NOT NULL,
  `declarant_name` varchar(150) NOT NULL,
  `declarant_designation` varchar(150) NOT NULL,
  `declarant_phone` varchar(20) NOT NULL,
  `declarant_email` varchar(150) NOT NULL,
  `declaration_date` date NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `employee_id` varchar(20) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('super_admin','admin','office_staff','sales_marketing','warehouse_staff') NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `address` text,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_id`, `profile_picture`, `full_name`, `email`, `phone`, `role`, `department`, `position`, `salary`, `joining_date`, `address`, `city`, `state`, `pincode`, `emergency_contact`, `emergency_contact_name`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'EMP156754', NULL, 'Test User', 'test@gmail.com', '9860409222', 'warehouse_staff', 'Test', '0', 20000.00, '2026-01-09', 'Test', 'test', 'test', '887766', '78768798', 'Test', 1, 2, '2026-01-09 07:20:26', '2026-01-09 07:20:26');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int NOT NULL,
  `invoice_no` varchar(50) NOT NULL,
  `invoice_type` enum('client','retailer') NOT NULL,
  `reference_id` int NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `financial_year` varchar(9) NOT NULL,
  `warehouse_id` int NOT NULL,
  `subtotal` decimal(12,2) DEFAULT '0.00',
  `cgst` decimal(12,2) DEFAULT '0.00',
  `sgst` decimal(12,2) DEFAULT '0.00',
  `igst` decimal(12,2) DEFAULT '0.00',
  `total` decimal(12,2) DEFAULT '0.00',
  `status` enum('draft','final','cancelled') DEFAULT 'draft',
  `payment_status` enum('pending','partial','paid') DEFAULT 'pending',
  `note` text,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `paid_amount` decimal(12,2) DEFAULT '0.00',
  `due_amount` decimal(12,2) DEFAULT '0.00'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `product_id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `rate` decimal(12,2) NOT NULL,
  `gst_percent` decimal(5,2) DEFAULT '0.00',
  `line_total` decimal(12,2) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_payments`
--

CREATE TABLE `invoice_payments` (
  `id` int NOT NULL,
  `invoice_id` int NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_mode` enum('cash','upi','bank','cheque','online') NOT NULL,
  `transaction_ref` varchar(255) DEFAULT NULL,
  `note` text,
  `received_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `login_history`
--

CREATE TABLE `login_history` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `login_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `logout_time` timestamp NULL DEFAULT NULL,
  `session_duration` int DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `model_agreements`
--

CREATE TABLE `model_agreements` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `consumer_number` varchar(100) NOT NULL,
  `applicant_address` text NOT NULL,
  `agreement_date` date NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `vendor_address` text NOT NULL,
  `system_capacity` decimal(6,2) NOT NULL,
  `pv_module_make` varchar(255) NOT NULL,
  `pv_module_capacity` varchar(100) NOT NULL,
  `panel_efficiency` varchar(50) NOT NULL,
  `inverter_company_name` varchar(255) NOT NULL,
  `inverter_capacity` varchar(100) NOT NULL,
  `system_cost` decimal(12,2) NOT NULL,
  `advance_percentage` int NOT NULL,
  `dispatch_percentage` int NOT NULL,
  `completion_percentage` int NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `net_metering_agreements`
--

CREATE TABLE `net_metering_agreements` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `consumer_name` varchar(255) NOT NULL,
  `consumer_number` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `location` varchar(150) NOT NULL,
  `system_capacity` decimal(6,2) NOT NULL,
  `vendor_name` varchar(255) NOT NULL,
  `agreement_date` date NOT NULL,
  `msedcl_representative` varchar(255) NOT NULL,
  `msedcl_designation` varchar(255) NOT NULL,
  `witness1_vendor` varchar(255) NOT NULL,
  `witness1_msedcl` varchar(255) NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `sku` varchar(120)  NOT NULL,
  `name` varchar(255)  NOT NULL,
  `brand` varchar(150)  DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `type` varchar(100)  DEFAULT NULL,
  `unit` varchar(50)  DEFAULT 'pc',
  `serial_tracked` tinyint(1) DEFAULT '0',
  `default_purchase_price` decimal(12,2) DEFAULT '0.00',
  `default_selling_price` decimal(12,2) DEFAULT '0.00',
  `tax_rate` decimal(5,2) DEFAULT '0.00',
  `hsn_code` varchar(50)  DEFAULT NULL,
  `weight_kg` decimal(8,3) DEFAULT NULL,
  `length_mm` int DEFAULT NULL,
  `width_mm` int DEFAULT NULL,
  `height_mm` int DEFAULT NULL,
  `warranty_months` int DEFAULT NULL,
  `description` text ,
  `specs` json DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `attr_key` varchar(120) NOT NULL,
  `attr_value` varchar(255) NOT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int NOT NULL,
  `name` varchar(150)  NOT NULL,
  `slug` varchar(150)  DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `filename` varchar(255)  NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `caption` varchar(255)  DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `product_serials`
--

CREATE TABLE `product_serials` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `warehouse_id` int DEFAULT NULL,
  `serial_number` varchar(255)  NOT NULL,
  `status` enum('in_stock','sold','transferred','reserved','damaged','returned')  NOT NULL DEFAULT 'in_stock',
  `notes` text ,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `product_suppliers`
--

CREATE TABLE `product_suppliers` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `supplier_id` int NOT NULL,
  `supplier_sku` varchar(150) DEFAULT NULL,
  `purchase_price` decimal(12,2) DEFAULT '0.00',
  `min_order_qty` int DEFAULT '0',
  `lead_time_days` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_address` text,
  `property_type` enum('residential','commercial','industrial') NOT NULL,
  `roof_type` enum('concrete','metal','tiled','asphalt') NOT NULL,
  `meter_type` enum('single_phase','three_phase','ct_meter','ip_meter') NOT NULL,
  `roof_area` decimal(10,2) DEFAULT NULL,
  `system_size` decimal(8,2) NOT NULL,
  `panel_company` varchar(50) NOT NULL,
  `inverter_company` varchar(50) NOT NULL,
  `panel_model` varchar(20) NOT NULL,
  `system_type` enum('on-grid','off-grid','hybrid') NOT NULL,
  `monthly_bill` decimal(10,2) NOT NULL,
  `battery_backup` tinyint(1) DEFAULT '0',
  `monitoring_system` tinyint(1) DEFAULT '0',
  `maintenance_package` tinyint(1) DEFAULT '0',
  `total_cost` decimal(12,2) NOT NULL,
  `subsidy` decimal(12,2) NOT NULL,
  `final_cost` decimal(12,2) NOT NULL,
  `monthly_savings` decimal(10,2) NOT NULL,
  `yearly_savings` decimal(10,2) NOT NULL,
  `payback_period` decimal(6,2) NOT NULL,
  `status` enum('draft','sent','viewed','negotiation','accepted','rejected') DEFAULT 'draft',
  `follow_up_date` date DEFAULT NULL,
  `notes` text,
  `created_by` int NOT NULL,
  `assigned_to` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sent_at` timestamp NULL DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `quotation_status_history`
--

CREATE TABLE `quotation_status_history` (
  `id` int NOT NULL,
  `quotation_id` int NOT NULL,
  `old_status` varchar(50) DEFAULT NULL,
  `new_status` varchar(50) NOT NULL,
  `changed_by` int NOT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `solar_panels`
--

CREATE TABLE `solar_panels` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `panel_number` int NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `wattage` int NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `solar_rooftop_quotations`
--

CREATE TABLE `solar_rooftop_quotations` (
  `quotation_id` int NOT NULL,
  `quote_number` varchar(50) NOT NULL,
  `status` enum('sent','approved','declined','under_review') DEFAULT 'sent',
  `customer_id` int DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_address` text,
  `prepared_by` varchar(150) DEFAULT NULL,
  `preparer_address` text,
  `preparer_contact` varchar(50) DEFAULT NULL,
  `preparer_email` varchar(150) DEFAULT NULL,
  `roof_type` varchar(50) DEFAULT NULL,
  `roof_area_sqft` decimal(8,2) DEFAULT NULL,
  `property_type` varchar(50) DEFAULT NULL,
  `meter_type` varchar(50) DEFAULT NULL,
  `monthly_bill` decimal(12,2) DEFAULT NULL,
  `panel_wattage` int DEFAULT NULL,
  `panel_count` int DEFAULT NULL,
  `panel_company` varchar(100) DEFAULT NULL,
  `inverter_company` varchar(100) DEFAULT NULL,
  `inverter_capacity` varchar(50) DEFAULT NULL,
  `inverter_type` varchar(50) DEFAULT NULL,
  `inverter_count` int NOT NULL,
  `system_size_kwp` decimal(6,2) DEFAULT NULL,
  `system_type` varchar(50) DEFAULT NULL,
  `total_cost` decimal(12,2) DEFAULT NULL,
  `subsidy` decimal(12,2) DEFAULT NULL,
  `final_cost` decimal(12,2) DEFAULT NULL,
  `monthly_savings` decimal(12,2) DEFAULT NULL,
  `payback_period` decimal(4,1) DEFAULT NULL,
  `created_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_date` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `battery_backup` tinyint(1) DEFAULT '0',
  `smart_monitoring` tinyint(1) DEFAULT '0',
  `annual_maintenance` tinyint(1) DEFAULT '0'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int NOT NULL,
  `movement_type` enum('purchase','adjustment','transfer_out','transfer_in','sale','consume','return','other')  NOT NULL,
  `product_id` int NOT NULL,
  `warehouse_from` int DEFAULT NULL,
  `warehouse_to` int DEFAULT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `unit` varchar(30)  DEFAULT 'pc',
  `note` text ,
  `related_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `reference_type` enum('client','retailer')  DEFAULT NULL,
  `reference_id` int DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `undertaking_documents`
--

CREATE TABLE `undertaking_documents` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `consumer_name` varchar(255) NOT NULL,
  `consumer_number` varchar(100) DEFAULT NULL,
  `project_address` text NOT NULL,
  `system_capacity` decimal(6,2) NOT NULL,
  `undertaking_date` date NOT NULL,
  `authorized_person` varchar(150) NOT NULL,
  `designation` varchar(150) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `role` enum('super_admin','admin','office_staff','sales_marketing','warehouse_staff') NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `role`, `is_active`, `last_login`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'admin@vksolar', 'admin123', 'Super Administrators', '1234567890', 'super_admin', 1, NULL, 1, '2025-10-13 03:48:04', '2026-01-01 11:36:01'),
(2, 'admin', 'admin@admin', 'admin123', 'VILAS KALE', '9657135476', 'admin', 1, NULL, 1, '2025-10-13 12:25:14', '2026-01-10 10:08:48'),
(8, 'staff1', 'onkarpadman13@gamail.com', '$2y$10$/EGT6s0HN/vu8JFILb0hvO6lTets912sFpV0rWRHRC/bA7wgOXrtq', 'onkar padman', '07666396753', 'office_staff', 1, NULL, 2, '2025-11-11 07:26:20', '2025-11-11 07:30:22'),
(12, 'officestaff', 'officestaff@gmail.com', 'office123', 'aniket', '9765778349', 'office_staff', 1, NULL, 2, '2025-12-16 07:20:33', '2025-12-16 07:22:50'),
(13, 'harishkadu', 'kaduharish55@gmail.com', '$2y$10$nSi.RNWeMoY/Q09NJuEPb.8/dh/mKL3a.kq1l8UiHTFLApUhqADJC', 'harish kadu', '9075305275', 'office_staff', 1, NULL, 2, '2025-12-16 09:45:07', '2025-12-16 09:50:02'),
(16, 'test@gmail.com', 'test@gmail.com', '$2y$10$ZOntdkwOKFpCaGkMCq5CZ.I3JqpV/koqiw8sfScTCUPjZakKmV5/.', 'Test User', '9860409222', 'warehouse_staff', 1, NULL, 2, '2026-01-09 07:20:26', '2026-01-09 07:20:26'),
(17, 'staff', 'adityathakre890776@gmail.com', '$2y$10$J.hoBVvwsuItVEEOwTwIZ.hk4BjGH.Rh.ArgH./MFWVi1BcVKwSVu', 'Aditya Prashant Thakare', '8010143603', 'office_staff', 1, NULL, 2, '2026-01-09 13:23:15', '2026-01-09 13:23:15'),
(18, 'admin123', 'adityathakre776@gmail.com', '$2y$10$d1/psQPDb4oWMtbFfKVJb.aqAWBWrL0quyZCKwgk6c6bP5bKhFHwm', 'Ojas', '8010143603', 'office_staff', 1, NULL, 2, '2026-01-09 13:25:19', '2026-01-09 13:25:19'),
(19, 'superadmin123', 'adityathaewkre776@gmail.com', '$2y$10$S4cMwxbOp3bQ76uW5vqQBOORFQZEM78qTUjJ/00ixkmHhj5K01LLi', 'Abhishek', '8110143603', 'office_staff', 1, NULL, 2, '2026-01-09 13:26:42', '2026-01-09 13:26:42'),
(20, 'sales', 'adityathakrergfrefe776@gmail.com', '$2y$10$oipf70y116EKt9YU7i0tPuFyGud3q6NmyTQ5.BNIukKWcwoygU73m', 'Aditya Prashant Thakarddd', '8010143603', 'sales_marketing', 1, NULL, 1, '2026-01-09 14:53:07', '2026-01-09 14:53:07');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` int NOT NULL,
  `role` enum('super_admin','admin','office_staff','sales_marketing','warehouse_staff')  NOT NULL,
  `module` varchar(50) NOT NULL,
  `can_view` tinyint(1) DEFAULT '0',
  `can_create` tinyint(1) DEFAULT '0',
  `can_edit` tinyint(1) DEFAULT '0',
  `can_delete` tinyint(1) DEFAULT '0'
) ;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`id`, `role`, `module`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 'super_admin', 'dashboard', 1, 1, 1, 1),
(2, 'super_admin', 'user_management', 1, 1, 1, 1),
(3, 'super_admin', 'employee_management', 1, 1, 1, 1),
(4, 'super_admin', 'quotation_management', 1, 1, 1, 1),
(5, 'super_admin', 'customer_management', 1, 1, 1, 1),
(6, 'super_admin', 'inventory_management', 1, 1, 1, 1),
(7, 'super_admin', 'reports', 1, 1, 1, 1),
(8, 'super_admin', 'invoice_management', 1, 1, 1, 1),
(9, 'admin', 'dashboard', 1, 1, 1, 1),
(10, 'admin', 'user_management', 1, 1, 1, 0),
(11, 'admin', 'employee_management', 1, 1, 1, 1),
(12, 'admin', 'quotation_management', 1, 1, 1, 1),
(13, 'admin', 'customer_management', 1, 1, 1, 1),
(14, 'admin', 'inventory_management', 1, 1, 1, 1),
(15, 'admin', 'reports', 1, 1, 1, 1),
(16, 'admin', 'invoice_management', 1, 1, 1, 1),
(17, 'office_staff', 'dashboard', 1, 0, 0, 0),
(18, 'office_staff', 'user_management', 0, 0, 0, 0),
(19, 'office_staff', 'employee_management', 0, 0, 1, 0),
(20, 'office_staff', 'quotation_management', 1, 1, 1, 0),
(21, 'office_staff', 'customer_management', 1, 1, 1, 0),
(22, 'office_staff', 'inventory_management', 1, 0, 0, 0),
(23, 'office_staff', 'reports', 1, 1, 0, 0),
(24, 'office_staff', 'settings', 0, 0, 0, 0),
(25, 'sales_marketing', 'dashboard', 1, 0, 0, 0),
(26, 'sales_marketing', 'user_management', 0, 0, 0, 0),
(27, 'sales_marketing', 'employee_management', 0, 0, 0, 0),
(28, 'sales_marketing', 'quotation_management', 1, 1, 1, 0),
(29, 'sales_marketing', 'customer_management', 1, 1, 1, 0),
(30, 'sales_marketing', 'inventory_management', 1, 0, 0, 0),
(31, 'sales_marketing', 'reports', 1, 0, 0, 0),
(32, 'sales_marketing', 'settings', 0, 0, 0, 0),
(33, 'warehouse_staff', 'dashboard', 1, 0, 0, 0),
(34, 'warehouse_staff', 'user_management', 0, 0, 0, 0),
(35, 'warehouse_staff', 'employee_management', 0, 0, 0, 0),
(36, 'warehouse_staff', 'quotation_management', 0, 0, 0, 0),
(37, 'warehouse_staff', 'customer_management', 0, 0, 0, 0),
(38, 'warehouse_staff', 'inventory_management', 1, 1, 1, 0),
(39, 'warehouse_staff', 'reports', 1, 0, 0, 0),
(40, 'warehouse_staff', 'settings', 0, 0, 0, 0),
(41, 'super_admin', 'bank_details_management', 1, 1, 1, 1),
(42, 'admin', 'bank_details_management', 1, 1, 1, 1),
(43, 'office_staff', 'bank_details_management', 1, 1, 1, 0),
(44, 'sales_marketing', 'bank_details_management', 1, 1, 1, 0),
(45, 'warehouse_staff', 'bank_details_management', 1, 0, 0, 0),
(76, 'office_staff', 'invoice_management', 1, 1, 1, 0),
(77, 'sales_marketing', 'invoice_management', 1, 0, 0, 0),
(78, 'warehouse_staff', 'invoice_management', 1, 0, 0, 0),
(91, 'super_admin', 'settings', 1, 1, 1, 1),
(92, 'admin', 'settings', 1, 1, 1, 1),
(94, 'super_admin', 'product_management', 1, 1, 1, 1),
(95, 'admin', 'product_management', 1, 1, 1, 1),
(96, 'office_staff', 'product_management', 1, 1, 1, 0),
(97, 'sales_marketing', 'product_management', 1, 0, 0, 0),
(98, 'warehouse_staff', 'product_management', 1, 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `address` text,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `emergency_contact` varchar(15) DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`id`, `user_id`, `address`, `city`, `state`, `pincode`, `profile_picture`, `date_of_birth`, `joining_date`, `salary`, `emergency_contact`, `emergency_contact_name`, `created_at`, `updated_at`) VALUES
(1, 1, 'VK Solar Energy Office', NULL, NULL, NULL, NULL, NULL, '2025-10-13', NULL, NULL, NULL, '2025-10-13 03:48:05', '2025-10-13 03:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `address` text,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(12) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `code`, `address`, `city`, `state`, `pincode`, `contact_name`, `contact_phone`, `image`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Test', '3323', 'test', 'test', 'test', '666666', 'test', '5675675676', '', NULL, '2026-01-09 07:21:06', '2026-01-09 07:21:06'),
(2, 'Test2', '56756', 'test2', 'test2', 'test2', '433433', 'test2', '433434343', '', NULL, '2026-01-09 07:21:44', '2026-01-09 07:21:44');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_employees`
--

CREATE TABLE `warehouse_employees` (
  `id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `role` varchar(50) DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `warehouse_employees`
--

INSERT INTO `warehouse_employees` (`id`, `warehouse_id`, `employee_id`, `role`, `assigned_at`) VALUES
(1, 1, 1, NULL, '2026-01-09 07:21:06');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_stock`
--

CREATE TABLE `warehouse_stock` (
  `id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT '0.000',
  `reserved` decimal(12,3) NOT NULL DEFAULT '0.000',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_stock_snapshots`
--

CREATE TABLE `warehouse_stock_snapshots` (
  `id` int NOT NULL,
  `warehouse_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` decimal(12,3) NOT NULL,
  `snapshot_at` datetime DEFAULT CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Table structure for table `work_completion_reports`
--

CREATE TABLE `work_completion_reports` (
  `id` int NOT NULL,
  `client_id` int NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `consumer_number` varchar(50) DEFAULT NULL,
  `address` text,
  `category` varchar(50) DEFAULT NULL,
  `sanction_number` varchar(100) DEFAULT NULL,
  `sanctioned_capacity` decimal(6,2) DEFAULT NULL,
  `installed_capacity` decimal(6,2) DEFAULT NULL,
  `module_make` varchar(150) DEFAULT NULL,
  `almm_model` varchar(100) DEFAULT NULL,
  `wattage_per_module` int DEFAULT NULL,
  `number_of_modules` int DEFAULT NULL,
  `total_capacity` decimal(6,2) DEFAULT NULL,
  `warranty_details` text,
  `inverter_make_model` varchar(150) DEFAULT NULL,
  `inverter_rating` varchar(100) DEFAULT NULL,
  `charge_controller` varchar(100) DEFAULT NULL,
  `inverter_capacity` varchar(100) DEFAULT NULL,
  `hpd` varchar(50) DEFAULT NULL,
  `manufacturing_year` int DEFAULT NULL,
  `earthings_count` int DEFAULT NULL,
  `lightening_arrester` varchar(50) DEFAULT NULL,
  `vendor_name` varchar(150) DEFAULT NULL,
  `aadhar_number` varchar(20) DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank_quotations`
--
ALTER TABLE `bank_quotations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank_quotation_products`
--
ALTER TABLE `bank_quotation_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_documents`
--
ALTER TABLE `client_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `commissioning_certificates`
--
ALTER TABLE `commissioning_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_client` (`client_id`);

--
-- Indexes for table `company_bank_details`
--
ALTER TABLE `company_bank_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `source_quotation_id` (`source_quotation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `declaration_documents`
--
ALTER TABLE `declaration_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_client_declaration` (`client_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `invoice_no_2` (`invoice_no`),
  ADD KEY `reference_id` (`reference_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `login_history`
--
ALTER TABLE `login_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `model_agreements`
--
ALTER TABLE `model_agreements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_client` (`client_id`);

--
-- Indexes for table `net_metering_agreements`
--
ALTER TABLE `net_metering_agreements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_id` (`client_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `sku_2` (`sku`),
  ADD KEY `name` (`name`),
  ADD KEY `type` (`type`),
  ADD KEY `brand` (`brand`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `attr_key` (`attr_key`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Indexes for table `product_suppliers`
--
ALTER TABLE `product_suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `quotation_number` (`quotation_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `quotation_status_history`
--
ALTER TABLE `quotation_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `solar_panels`
--
ALTER TABLE `solar_panels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_client_panel` (`client_id`,`panel_number`),
  ADD KEY `idx_serial_number` (`serial_number`);

--
-- Indexes for table `solar_rooftop_quotations`
--
ALTER TABLE `solar_rooftop_quotations`
  ADD PRIMARY KEY (`quotation_id`),
  ADD UNIQUE KEY `quote_number` (`quote_number`),
  ADD KEY `customer_name` (`customer_name`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `quote_number_2` (`quote_number`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `warehouse_from` (`warehouse_from`),
  ADD KEY `warehouse_to` (`warehouse_to`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `undertaking_documents`
--
ALTER TABLE `undertaking_documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_undertaking_client` (`client_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_module` (`role`,`module`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_warehouse_code` (`code`);

--
-- Indexes for table `warehouse_employees`
--
ALTER TABLE `warehouse_employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_warehouse_employee` (`warehouse_id`,`employee_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_warehouse_product` (`warehouse_id`,`product_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `warehouse_stock_snapshots`
--
ALTER TABLE `warehouse_stock_snapshots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `work_completion_reports`
--
ALTER TABLE `work_completion_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_id` (`client_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank_quotations`
--
ALTER TABLE `bank_quotations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `bank_quotation_products`
--
ALTER TABLE `bank_quotation_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `client_documents`
--
ALTER TABLE `client_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `commissioning_certificates`
--
ALTER TABLE `commissioning_certificates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `company_bank_details`
--
ALTER TABLE `company_bank_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `declaration_documents`
--
ALTER TABLE `declaration_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_history`
--
ALTER TABLE `login_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `model_agreements`
--
ALTER TABLE `model_agreements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `net_metering_agreements`
--
ALTER TABLE `net_metering_agreements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_serials`
--
ALTER TABLE `product_serials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_suppliers`
--
ALTER TABLE `product_suppliers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quotation_status_history`
--
ALTER TABLE `quotation_status_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `solar_panels`
--
ALTER TABLE `solar_panels`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `solar_rooftop_quotations`
--
ALTER TABLE `solar_rooftop_quotations`
  MODIFY `quotation_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `undertaking_documents`
--
ALTER TABLE `undertaking_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `warehouse_employees`
--
ALTER TABLE `warehouse_employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warehouse_stock_snapshots`
--
ALTER TABLE `warehouse_stock_snapshots`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `work_completion_reports`
--
ALTER TABLE `work_completion_reports`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_quotation_products`
--
ALTER TABLE `bank_quotation_products`
  ADD CONSTRAINT `bank_quotation_products_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `bank_quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`source_quotation_id`) REFERENCES `quotations` (`id`),
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `declaration_documents`
--
ALTER TABLE `declaration_documents`
  ADD CONSTRAINT `declaration_documents_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoice_payments`
--
ALTER TABLE `invoice_payments`
  ADD CONSTRAINT `invoice_payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_history`
--
ALTER TABLE `login_history`
  ADD CONSTRAINT `login_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `net_metering_agreements`
--
ALTER TABLE `net_metering_agreements`
  ADD CONSTRAINT `net_metering_agreements_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD CONSTRAINT `product_attributes_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_serials`
--
ALTER TABLE `product_serials`
  ADD CONSTRAINT `product_serials_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_serials_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_suppliers`
--
ALTER TABLE `product_suppliers`
  ADD CONSTRAINT `product_suppliers_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_suppliers_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`);

--
-- Constraints for table `quotation_status_history`
--
ALTER TABLE `quotation_status_history`
  ADD CONSTRAINT `quotation_status_history_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  ADD CONSTRAINT `quotation_status_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `solar_panels`
--
ALTER TABLE `solar_panels`
  ADD CONSTRAINT `solar_panels_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`warehouse_from`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`warehouse_to`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `undertaking_documents`
--
ALTER TABLE `undertaking_documents`
  ADD CONSTRAINT `undertaking_documents_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `user_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouse_employees`
--
ALTER TABLE `warehouse_employees`
  ADD CONSTRAINT `warehouse_employees_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_employees_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouse_stock`
--
ALTER TABLE `warehouse_stock`
  ADD CONSTRAINT `warehouse_stock_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_stock_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouse_stock_snapshots`
--
ALTER TABLE `warehouse_stock_snapshots`
  ADD CONSTRAINT `warehouse_stock_snapshots_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `warehouse_stock_snapshots_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_completion_reports`
--
ALTER TABLE `work_completion_reports`
  ADD CONSTRAINT `work_completion_reports_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
