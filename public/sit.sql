-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 06, 2026 at 08:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sit`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `log_name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `subject_type` varchar(255) DEFAULT NULL,
  `event` varchar(255) DEFAULT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `causer_type` varchar(255) DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `properties` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`properties`)),
  `batch_uuid` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_marks`
--

CREATE TABLE `activity_marks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_activity_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `score` decimal(6,2) NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tracking_id` varchar(255) NOT NULL,
  `identity_number` varchar(255) DEFAULT NULL,
  `student_name` varchar(255) NOT NULL,
  `date_of_birth` date NOT NULL,
  `applied_grade` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `guardian_name` varchar(255) NOT NULL,
  `guardian_phone` varchar(255) NOT NULL,
  `guardian_email` varchar(255) DEFAULT NULL,
  `previous_school` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subjects_passed` text NOT NULL,
  `results_file` varchar(255) DEFAULT NULL,
  `recommendation_letter` varchar(255) DEFAULT NULL,
  `academic_history` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admissions`
--

INSERT INTO `admissions` (`id`, `tracking_id`, `identity_number`, `student_name`, `date_of_birth`, `applied_grade`, `address`, `guardian_name`, `guardian_phone`, `guardian_email`, `previous_school`, `status`, `admin_remarks`, `created_at`, `updated_at`, `subjects_passed`, `results_file`, `recommendation_letter`, `academic_history`) VALUES
(1, 'KPC-2026-ODMYRS', '12345678D9', 'Musa Elias Mukahlera', '2008-09-24', 'Form 1', NULL, 'Musa Kufa', '0787247792', NULL, NULL, 'approved', 'Congradulation on being part of us', '2026-02-05 16:14:19', '2026-02-06 09:06:34', 'Maths D\r\nEnglish E', 'admissions/results/aI8hSUKzYlRqX2dyUNKX9LmX12kbCV5yNjLAb7iz.jpg', 'admissions/recommendations/VlKIId3rQ4Dj3MNjU6iVVB4PU5yVxY02wVZ7Sc7w.jpg', NULL),
(2, 'KPC-2026-RO7VKR', '212223A24', 'Neville Mupasa', '2026-02-18', 'Form 1', '3094 Budiriro 2', 'Musa Elias Mukahlera', '+263787247792', 'musamukahlera@gmail.com', 'Masvingo Day School', 'approved', NULL, '2026-02-22 09:22:16', '2026-09-06 13:26:45', '8 Subjects', 'admissions/results/kz2Vu1xY9b4uJ9arg5HjMgaaSoNNKYdEFv6RGFVu.png', 'admissions/recommendations/Hi5vyYJF1F9kaF5kWpUb1tqyhCCQq00hLBHXIEhf.png', 'qwsedfghj'),
(3, 'KPC-2026-NFN8ZY', '12345678D12', 'Tafadzwa Mukahlera', '2026-02-13', 'Form 1', 'Mutondwe', 'MUSA ELIAS MUKAHLERA', '07455855662', 'b240336a@students.buse.ac.zw', 'ghgfd', 'approved', NULL, '2026-02-27 17:58:10', '2026-02-27 18:00:27', '7 Sun', 'admissions/results/tdZC4FCmD5fEQ0RaNbcDdKscmySWfhdDkKUXOSjg.jpg', NULL, 'wsedsrfghfdsa'),
(4, 'KPC-2026-G2OTQM', '12345678Q9', 'Takudzwa Masiwa', '2004-09-02', 'Form 2', '3094 Budiriro 2', 'Punha Masiwa', '+263787247792', 'punhamasiwa@gmail.com', 'Masvingo Day School', 'approved', NULL, '2026-03-04 18:03:16', '2026-03-30 16:38:31', '8 Subjects', 'admissions/results/h9hmZigjzM4qp7yd774xw2FptoKLjmfYFppvpW3N.png', NULL, 'qawsedtfghj');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('elias-academy-college-portal-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:3:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";}s:11:\"permissions\";a:10:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"admissions\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"manage-users\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:21:\"roles_and_permissions\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:18:\"student_management\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:11:\"manage-fees\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"term_management\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:8;s:1:\"b\";s:7:\"payroll\";s:1:\"c\";s:3:\"web\";}i:7;a:3:{s:1:\"a\";i:9;s:1:\"b\";s:16:\"manage-inventory\";s:1:\"c\";s:3:\"web\";}i:8;a:3:{s:1:\"a\";i:10;s:1:\"b\";s:14:\"manage-payroll\";s:1:\"c\";s:3:\"web\";}i:9;a:3:{s:1:\"a\";i:11;s:1:\"b\";s:15:\"manage-expenses\";s:1:\"c\";s:3:\"web\";}}s:5:\"roles\";a:0:{}}', 1788805584);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_activities`
--

CREATE TABLE `class_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_assignment_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('classwork','homework','quiz','participation','practical','project','other') NOT NULL DEFAULT 'classwork',
  `activity_date` date NOT NULL,
  `max_score` smallint(5) UNSIGNED NOT NULL DEFAULT 100,
  `weight` decimal(5,2) NOT NULL DEFAULT 1.00,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_subject`
--

CREATE TABLE `class_subject` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `school_class_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class_subject`
--

INSERT INTO `class_subject` (`id`, `school_class_id`, `subject_id`, `created_at`, `updated_at`) VALUES
(4, 15, 1, NULL, NULL),
(5, 15, 2, NULL, NULL),
(6, 16, 1, NULL, NULL),
(7, 15, 3, NULL, NULL),
(8, 15, 4, NULL, NULL),
(9, 15, 5, NULL, NULL),
(10, 15, 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_name` varchar(255) NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `exam_date` date NOT NULL,
  `status` enum('pending','published') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `subject_assignment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_attendances`
--

CREATE TABLE `exam_attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `verified_by` bigint(20) UNSIGNED NOT NULL,
  `verification_method` varchar(255) NOT NULL DEFAULT 'face_scan',
  `verified_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('present','flagged','absent') NOT NULL DEFAULT 'present',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `expense_date` date NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `description`, `amount`, `expense_date`, `category`, `reference_no`, `notes`, `created_at`, `updated_at`) VALUES
(3, 'School Bus Fuel Costs', 300.00, '2026-09-04', 'Other', 'TOTAL123', 'LKFLQF', '2026-09-04 08:49:47', '2026-09-04 08:49:47');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_structures`
--

CREATE TABLE `fee_structures` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `fee_name` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `grade` varchar(255) NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `student_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fee_structures`
--

INSERT INTO `fee_structures` (`id`, `fee_name`, `amount`, `grade`, `term_id`, `created_at`, `updated_at`, `student_id`) VALUES
(69, 'Tution', 100.00, 'Form 1', 2, '2026-09-03 11:37:10', '2026-09-03 11:37:10', NULL),
(70, 'Tution', 100.00, 'Form 2', 2, '2026-09-03 11:37:10', '2026-09-03 11:37:10', NULL),
(71, 'Sporting Levy', 30.00, 'Form 1', 2, '2026-09-03 11:37:22', '2026-09-03 11:37:22', NULL),
(72, 'Sporting Levy', 30.00, 'Form 2', 2, '2026-09-03 11:37:22', '2026-09-03 11:37:22', NULL),
(73, 'Transportation', 10.00, 'Form 1', 2, '2026-09-03 11:37:53', '2026-09-03 11:37:53', NULL),
(74, 'Transportation', 10.00, 'Form 2', 2, '2026-09-03 11:37:53', '2026-09-03 11:37:53', NULL),
(75, 'SDC', 10.00, 'Form 1', 2, '2026-09-03 11:38:12', '2026-09-03 11:38:12', NULL),
(76, 'SDC', 10.00, 'Form 2', 2, '2026-09-03 11:38:13', '2026-09-03 11:38:13', NULL),
(77, 'School Fees', 150.00, 'Form 1', 3, '2026-09-03 11:47:31', '2026-09-03 11:47:31', NULL),
(78, 'School Fees', 150.00, 'Form 2', 3, '2026-09-03 11:47:31', '2026-09-03 11:47:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fee_transactions`
--

CREATE TABLE `fee_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `channel` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `poll_url` text DEFAULT NULL,
  `paynow_reference` varchar(255) DEFAULT NULL,
  `payer_phone` varchar(255) DEFAULT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `sku` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `alert_level` int(11) NOT NULL DEFAULT 5,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_name`, `sku`, `category`, `quantity`, `alert_level`, `unit_price`, `created_at`, `updated_at`) VALUES
(1, 'tie', 'tb1', 'Uniforms', 2, 5, 5.00, '2026-01-27 19:30:57', '2026-02-07 16:46:42');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_stocks`
--

CREATE TABLE `inventory_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `inventory_item_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('in','out') NOT NULL,
  `quantity` int(11) NOT NULL,
  `person_involved` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_stocks`
--

INSERT INTO `inventory_stocks` (`id`, `inventory_item_id`, `type`, `quantity`, `person_involved`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 'in', 10, 'Leon Chirove', NULL, '2026-01-27 19:37:58', '2026-01-27 19:37:58'),
(2, 1, 'in', 2, 'Musa Elias Mukahlera', NULL, '2026-02-07 16:46:16', '2026-02-07 16:46:16'),
(3, 1, 'out', 10, 'Musa Elias Mukahlera', 'sold 10', '2026-02-07 16:46:42', '2026-02-07 16:46:42');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `marks`
--

CREATE TABLE `marks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `exam_id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `score` int(11) NOT NULL,
  `max_score` int(11) NOT NULL DEFAULT 100,
  `teacher_comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_01_03_200751_update_users', 2),
(10, '2026_01_04_111804_create_students_table', 3),
(11, '2026_01_04_121452_create_table_classes', 3),
(12, '2026_01_04_130405_create_subjects_table', 4),
(14, '2026_01_04_135818_create_class_subject_table', 5),
(15, '2026_01_04_145013_create_terms_table', 6),
(16, '2026_01_04_145757_add_term_id_to_students', 6),
(17, '2026_01_04_153051_add_enrollment_fields_to_students_table', 7),
(18, '2026_01_04_161352_create_fees_and_payments_tables', 8),
(19, '2026_01_04_203511_create_exams_and_marks_tables', 9),
(20, '2026_01_04_214333_add_subject_id_to_exams_table', 10),
(21, '2026_01_05_090903_create_settings_table', 11),
(22, '2026_01_05_093457_add_auth_to_students_table', 11),
(23, '2026_01_05_102642_add_balance_to_students', 12),
(24, '2026_01_05_201635_add_role_to_users_table', 13),
(25, '2026_01_06_104134_add_class_id_to_students_table', 14),
(26, '2026_01_06_105106_add_class_id_to_students_table', 15),
(27, '2026_01_08_125948_add_student_id_to_fee_structures_table', 16),
(28, '2026_01_27_204119_add_payroll_fields_to_users_table', 17),
(29, '2026_01_27_204318_create_payslips_table', 18),
(30, '2026_01_27_211219_create_inventory_items_table', 19),
(31, '2026_01_27_211721_create_inventory_stocks_table', 20),
(32, '2026_02_04_194949_create_expenses_table', 21),
(33, '2026_02_05_124104_create_admissions_table', 22),
(34, '2026_02_05_125616_add_files_to_admissions_table', 23),
(35, '2026_02_05_131411_add_tracking_id_to_admissions_table', 24),
(36, '2026_02_05_132255_add_identity_number_to_admissions_table', 25),
(37, '2026_02_05_181018_add_missing_fields_to_admissions_table', 26),
(38, '2026_02_05_210008_change_academic_history_to_text_in_admissions_table', 27),
(39, '2026_02_07_162759_add_national_id_to_users_table', 28),
(40, '2026_02_07_163240_modify_ec_number_on_users_table', 29),
(41, '2026_02_07_163353_fix_users_table_for_students', 30),
(42, '2026_02_09_154611_create_subject_assignments_table', 31),
(43, '2026_02_11_113707_add_assignment_to_exams', 32),
(44, '2026_02_15_091630_add_received_by_to_payments_table', 33),
(45, '2026_02_22_111024_add_missing_fields_to_admissions_table', 34),
(46, '2026_02_22_111257_add_missing_fields_to_admissions_table', 35),
(47, '2026_02_25_124652_update_students_table_change_age_to_dob', 36),
(48, '2026_02_25_173006_rename_employee_number_to_dob_on_users_table', 37),
(49, '2026_02_25_173011_rename_employee_number_to_dob_on_users_table', 37),
(50, '2026_03_04_145610_timetables', 38),
(51, '2026_03_04_175042_make_timetable_fields_nullable', 39),
(52, '2026_03_05_095050_add_special_type_to_timetables_table', 40),
(53, '2026_06_18_105852_create_exam_attendances_table', 41),
(54, '2026_06_20_150554_add_face_path_to_students_table', 42),
(55, '2026_06_20_153001_add_face_descriptor_to_students_table', 43),
(56, '2026_07_28_074018_fees_transactions', 44),
(57, '2026_08_25_120000_create_class_activities_table', 44),
(58, '2026_08_25_120001_create_activity_marks_table', 44),
(59, '2026_08_25_125946_create_activity_log_table', 44),
(60, '2026_08_25_125947_add_event_column_to_activity_log_table', 44),
(61, '2026_08_25_125948_add_batch_uuid_column_to_activity_log_table', 44),
(62, '2026_09_06_160914_create_permission_tables', 45);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 5),
(3, 'App\\Models\\User', 5),
(4, 'App\\Models\\User', 5),
(6, 'App\\Models\\User', 5),
(7, 'App\\Models\\User', 5),
(8, 'App\\Models\\User', 5),
(9, 'App\\Models\\User', 5),
(10, 'App\\Models\\User', 5),
(11, 'App\\Models\\User', 5);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 21),
(3, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(4, 'App\\Models\\User', 5);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_id` bigint(20) UNSIGNED NOT NULL,
  `term_id` bigint(20) UNSIGNED NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `reference_no` varchar(255) DEFAULT NULL,
  `received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `term_id`, `amount_paid`, `payment_date`, `payment_method`, `reference_no`, `received_by`, `remarks`, `created_at`, `updated_at`) VALUES
(77, 175, 2, -150.00, '2026-09-04', 'Term Invoice', 'INV-2-175', NULL, 'Term Fees Charged (Term 1)', '2026-09-04 08:03:12', '2026-09-04 08:03:12'),
(78, 176, 2, -150.00, '2026-09-04', 'Term Invoice', 'INV-2-176', NULL, 'Term Fees Charged (Term 1)', '2026-09-04 08:03:12', '2026-09-04 08:03:12'),
(79, 177, 2, -150.00, '2026-09-04', 'Term Invoice', 'INV-2-177', NULL, 'Term Fees Charged (Term 1)', '2026-09-04 08:03:12', '2026-09-04 08:03:12'),
(80, 178, 2, -150.00, '2026-09-04', 'Term Invoice', 'INV-2-178', NULL, 'Term Fees Charged (Term 1)', '2026-09-04 08:03:12', '2026-09-04 08:03:12'),
(81, 175, 3, 150.06, '2026-09-04', 'Cash', 'RC1230', NULL, 'School Fees', '2026-09-04 08:21:04', '2026-09-04 08:21:04'),
(82, 176, 2, 160.00, '2026-09-04', 'Cash', 'RC1231', NULL, '12341234', '2026-09-04 08:22:41', '2026-09-04 08:22:41'),
(83, 177, 3, 150.46, '2026-09-04', 'Cash', 'qweqe1', NULL, 'fees', '2026-09-04 08:23:29', '2026-09-04 08:23:29'),
(84, 178, 3, 299.82, '2026-09-04', 'Card', 'TXN-1234', NULL, '12341234', '2026-09-04 08:23:58', '2026-09-04 08:23:58'),
(85, 177, 3, 150.15, '2026-09-04', 'Cash', 'TXN-1234', NULL, '11223344', '2026-09-04 08:27:21', '2026-09-04 08:27:21'),
(86, 175, 3, -150.00, '2026-09-04', 'Term Invoice', 'INV-3-175', NULL, 'Term Fees Charged (Term 2)', '2026-09-04 08:28:14', '2026-09-04 08:28:14'),
(87, 176, 3, -150.00, '2026-09-04', 'Term Invoice', 'INV-3-176', NULL, 'Term Fees Charged (Term 2)', '2026-09-04 08:28:14', '2026-09-04 08:28:14'),
(88, 177, 3, -150.00, '2026-09-04', 'Term Invoice', 'INV-3-177', NULL, 'Term Fees Charged (Term 2)', '2026-09-04 08:28:14', '2026-09-04 08:28:14'),
(89, 178, 3, -150.00, '2026-09-04', 'Term Invoice', 'INV-3-178', NULL, 'Term Fees Charged (Term 2)', '2026-09-04 08:28:14', '2026-09-04 08:28:14'),
(90, 175, 3, 150.00, '2026-09-04', 'Cash', 'TXN-1234', NULL, '123123', '2026-09-04 08:30:37', '2026-09-04 08:30:37'),
(91, 176, 3, 150.00, '2026-09-04', 'Cash', NULL, NULL, NULL, '2026-09-04 08:31:47', '2026-09-04 08:31:47'),
(92, 178, 3, 600.00, '2026-09-04', 'ZIPIT', 'CBZsdkk123=', NULL, 'Fees/1231/232', '2026-09-04 08:34:02', '2026-09-04 08:34:02'),
(93, 175, 3, -0.06, '2026-09-04', 'Credit Withdrawal', 'WD-6A9A9ED2A4076', NULL, 'Refund to Parent', '2026-09-04 08:34:58', '2026-09-04 08:34:58'),
(94, 178, 3, -599.82, '2026-09-04', 'Credit Withdrawal', 'WD-6A9A9F35BDBC5', NULL, 'Refund to Parent', '2026-09-04 08:36:37', '2026-09-04 08:36:37'),
(95, 178, 3, 0.01, '2026-09-04', 'Cash', NULL, NULL, NULL, '2026-09-04 08:37:42', '2026-09-04 08:37:42'),
(96, 175, 2, 150.00, '2026-09-04', 'Cash', '123', NULL, '123', '2026-09-04 08:39:56', '2026-09-04 08:39:56'),
(97, 177, 3, 150.00, '2026-09-04', 'Cash', 'querty123', NULL, 'keyboard Investments', '2026-09-04 08:42:24', '2026-09-04 08:42:24'),
(98, 177, 3, -150.00, '2026-09-04', 'Credit Withdrawal', 'WD-6A9AA0BF8E652', NULL, 'SDK error (Reservsal Transaction)', '2026-09-04 08:43:11', '2026-09-04 08:43:11'),
(99, 177, 2, 150.00, '2026-09-04', 'Cash', 'querty123', NULL, '123123', '2026-09-04 08:43:43', '2026-09-04 08:43:43'),
(100, 178, 3, 150.00, '2026-09-04', 'Cash', 'querty123', NULL, 'grrwwgfwg', '2026-09-04 08:44:46', '2026-09-04 08:44:46'),
(101, 176, 3, 69.79, '2026-09-04', 'Cash', 'owvnwv', NULL, NULL, '2026-09-04 08:47:21', '2026-09-04 08:47:21'),
(102, 176, 2, 69.79, '2026-09-04', 'Cash', 'owvnwv', NULL, NULL, '2026-09-04 08:47:41', '2026-09-04 08:47:41');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pay_period` varchar(255) NOT NULL,
  `base_salary` decimal(10,2) NOT NULL,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`id`, `user_id`, `pay_period`, `base_salary`, `allowances`, `deductions`, `net_salary`, `payment_date`, `remarks`, `created_at`, `updated_at`) VALUES
(11, 5, 'September', 299.66, 60.00, 2.86, 356.80, '2026-09-06', 'bank', '2026-09-06 12:20:36', '2026-09-06 12:20:36');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admissions', 'web', '2026-09-06 15:06:05', '2026-09-06 15:06:05'),
(2, 'manage-users', 'web', '2026-09-06 15:06:32', '2026-09-06 15:06:32'),
(3, 'roles_and_permissions', 'web', '2026-09-06 15:06:54', '2026-09-06 15:06:54'),
(4, 'student_management', 'web', '2026-09-06 15:07:33', '2026-09-06 15:07:33'),
(6, 'manage-fees', 'web', '2026-09-06 16:07:48', '2026-09-06 16:07:48'),
(7, 'term_management', 'web', '2026-09-06 16:15:26', '2026-09-06 16:15:26'),
(8, 'payroll', 'web', '2026-09-06 16:19:22', '2026-09-06 16:19:22'),
(9, 'manage-inventory', 'web', '2026-09-06 16:20:42', '2026-09-06 16:20:42'),
(10, 'manage-payroll', 'web', '2026-09-06 16:20:53', '2026-09-06 16:20:53'),
(11, 'manage-expenses', 'web', '2026-09-06 16:21:07', '2026-09-06 16:21:07');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2026-09-06 14:34:46', '2026-09-06 14:34:46'),
(2, 'receptionist', 'web', '2026-09-06 14:34:47', '2026-09-06 14:34:47'),
(3, 'teacher', 'web', '2026-09-06 14:34:47', '2026-09-06 14:34:47'),
(4, 'super_admin', 'web', '2026-09-06 15:28:44', '2026-09-06 15:28:44'),
(5, 'clerk', 'web', '2026-09-06 16:14:52', '2026-09-06 16:14:52'),
(6, 'bursar', 'web', '2026-09-06 16:15:04', '2026-09-06 16:15:04'),
(7, 'librarian', 'web', '2026-09-06 16:24:56', '2026-09-06 16:24:56'),
(8, 'hod', 'web', '2026-09-06 16:25:08', '2026-09-06 16:25:08'),
(9, 'principal', 'web', '2026-09-06 16:26:12', '2026-09-06 16:26:12'),
(10, 'deputy head', 'web', '2026-09-06 16:26:23', '2026-09-06 16:26:23');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `school_classes`
--

CREATE TABLE `school_classes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_name` varchar(255) NOT NULL,
  `class_code` varchar(255) NOT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `capacity` int(11) NOT NULL DEFAULT 100,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `school_classes`
--

INSERT INTO `school_classes` (`id`, `class_name`, `class_code`, `room_number`, `capacity`, `teacher_id`, `status`, `created_at`, `updated_at`) VALUES
(15, 'Form 1', 'F1', '1', 40, 12, 'active', '2026-02-06 09:31:42', '2026-07-07 13:36:37'),
(16, 'Form 2', 'F2', '2', 60, NULL, 'active', '2026-02-06 09:52:40', '2026-02-06 09:52:40'),
(17, 'Form 3', 'F3', '3', 80, 13, 'active', '2026-02-11 07:52:36', '2026-02-11 10:42:04');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('tJxlqp5Zn9ZR72jxmpdVZDnddxE8OLw7xrGgdM21', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoieVdmdGZRa1h6QjE0UmVpVFpaQWMyWG1kNFl1cDc5VlZwMnRBZHRhNiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbmlzdHJhdGlvbi9yb2xlcyI7czo1OiJyb3V0ZSI7czoxMToicm9sZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O30=', 1788720355),
('YBicOVfGCaIU6JPsgTrAEbscdl95UB6cSs9KdR9y', 21, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiVVhKYmRVNmRpZmZkOGd4NjQ3a1VVNVZrVFd6QWNHdU84bElJSnYyWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9yZWNlcHRpb25pc3QvY2xhc3NlcyI7czo1OiJyb3V0ZSI7czoyNjoicmVjZXB0aW9uaXN0LmNsYXNzZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyMTt9', 1788718277),
('YcuYNkijcyHZ0Z6ezLmJDnkSnYmq8oknKjFuYrTN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 Edg/152.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGRVS29ES3BvdkdHY0d4N1BOYzU1eFNFb1ZnRGhpeGhVWHcwZkxMeSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1788718263);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `term_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_number` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) DEFAULT NULL,
  `national_id` varchar(255) DEFAULT NULL,
  `grade` varchar(255) DEFAULT NULL,
  `enrollment_status` varchar(255) NOT NULL DEFAULT 'active',
  `address` text DEFAULT NULL,
  `parent_contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `enrollment_date` date DEFAULT NULL,
  `enrollment_term_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `photo_path` varchar(255) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `face_path` varchar(255) DEFAULT NULL,
  `face_descriptor` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`face_descriptor`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `class_id`, `term_id`, `student_number`, `name`, `surname`, `date_of_birth`, `gender`, `national_id`, `grade`, `enrollment_status`, `address`, `parent_contact`, `email`, `phone`, `enrollment_date`, `enrollment_term_id`, `status`, `photo_path`, `emergency_contact`, `password`, `created_at`, `updated_at`, `remember_token`, `balance`, `face_path`, `face_descriptor`) VALUES
(175, 15, 2, 'EAC260001', 'Musa Elias', 'Mukahlera', '2004-09-24', 'Male', '222042235E22', 'Form 1', 'active', '3094 Budiriro 2\r\nOnline', 'Musa Elias Mukahlera', 'eac260001@eac.cac.zw', '+263787247792', '2026-09-03', NULL, 'active', NULL, 'Musa Elias Mukahlera', '$2y$12$pnV2tIL3Y3ogDAJp5xLsz.J53kr8EWBeaYJLeSIyxG0kbP/ZrRilm', '2026-09-03 11:30:25', '2026-09-03 11:30:25', NULL, 0.00, NULL, NULL),
(176, 16, 2, 'EAC260176', 'Hamamunashe', 'Tirekerwi', '2001-01-01', 'Male', '12121212D12', 'Form 2', 'active', '3094 Budiriro 2\r\nOnline', 'Musa Elias Mukahlera', 'eac260176@eac.cac.zw', '+263787247792', '2026-09-03', NULL, 'active', NULL, 'Musa Elias Mukahlera', '$2y$12$w7JioqHbY.aUUDG3q5mS2ONOD2p..d4Z430g2bvDp7a0his3wXQMO', '2026-09-03 11:33:57', '2026-09-03 11:33:57', NULL, 0.00, NULL, NULL),
(177, 15, 3, 'EAC260177', 'Munashe', 'Mbaimbai', '2007-12-01', 'Male', '112233445566778899P77', 'Form 1', 'active', '2011 kambuzuma road aspingdel', 'Mr Moyo 0787247792 Parent', 'eac260177@eac.cac.zw', '+263787950406', '2026-09-04', NULL, 'active', NULL, 'Mr Moyo 0787247792 Parent', '$2y$12$ro2535Tv8sJ2zy1KBvrJp.rCq/XIUnEoVoqJTB/gfiySBNo51NVsu', '2026-09-04 06:57:35', '2026-09-04 06:57:35', NULL, 0.00, NULL, NULL),
(178, 15, 3, 'EAC260178', 'John', 'Doe', '2007-12-01', 'Male', '18621122Q11', 'Form 1', 'active', 'Somewhere in Mars', 'Mr Moyo 0787247792 Parent', 'eac260178@eac.cac.zw', '+263112233445566', '2026-09-04', NULL, 'active', NULL, 'Mr Moyo 0787247792 Parent', '$2y$12$wXrUt0embMCGVbEG26nKfuQvI7qpJn80t5uP/Uo3F7/XLmSNUq2py', '2026-09-04 07:01:00', '2026-09-04 07:01:00', NULL, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `subject_code` varchar(255) NOT NULL,
  `type` enum('Core','Elective','Practical') NOT NULL DEFAULT 'Core',
  `pass_mark` int(11) NOT NULL DEFAULT 50,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_name`, `subject_code`, `type`, `pass_mark`, `created_at`, `updated_at`) VALUES
(1, 'Mathematics', 'Math_Ordinary', 'Core', 50, '2026-01-04 11:55:45', '2026-01-04 11:55:45'),
(2, 'English', 'ENG', 'Core', 50, '2026-01-05 17:21:01', '2026-01-05 17:21:01'),
(3, 'Computer Science', 'CS01', 'Practical', 50, '2026-02-09 14:16:25', '2026-02-09 14:16:25'),
(4, 'Combined Science', 'scie101', 'Core', 50, '2026-02-09 14:16:53', '2026-02-09 14:16:53'),
(5, 'Physics', 'SCIE102', 'Core', 50, '2026-02-09 14:17:13', '2026-02-09 14:17:13'),
(6, 'BIOLOGY', 'SCIE103', 'Core', 50, '2026-02-09 14:17:30', '2026-02-09 14:17:30');

-- --------------------------------------------------------

--
-- Table structure for table `subject_assignments`
--

CREATE TABLE `subject_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subject_assignments`
--

INSERT INTO `subject_assignments` (`id`, `teacher_id`, `subject_id`, `class_id`, `academic_year`, `created_at`, `updated_at`) VALUES
(1, 12, 1, 15, '2026', '2026-02-09 14:12:15', '2026-02-09 14:12:15'),
(2, 12, 5, 15, '2026', '2026-02-11 07:01:07', '2026-02-11 07:01:07'),
(3, 13, 3, 17, '2026', '2026-02-11 08:25:39', '2026-02-11 08:25:39'),
(4, 13, 5, 15, '2026', '2026-02-11 09:43:47', '2026-02-11 09:43:47');

-- --------------------------------------------------------

--
-- Table structure for table `terms`
--

CREATE TABLE `terms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `term_name` varchar(255) NOT NULL,
  `academic_year` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `is_current` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `terms`
--

INSERT INTO `terms` (`id`, `term_name`, `academic_year`, `start_date`, `end_date`, `is_current`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Term 1', '2026', '2026-01-01', '2026-02-05', 0, 'open', '2026-02-06 09:35:56', '2026-09-03 11:45:33'),
(3, 'Term 2', '2026', '2026-02-06', '2026-02-09', 1, 'open', '2026-02-06 09:36:13', '2026-09-03 11:45:34');

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `class_id` bigint(20) UNSIGNED NOT NULL,
  `subject_id` bigint(20) UNSIGNED DEFAULT NULL,
  `teacher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'SUBJECT',
  `special_type` varchar(255) DEFAULT NULL,
  `room_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dob` date DEFAULT NULL,
  `employee_id` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `national_id` varchar(255) DEFAULT NULL,
  `base_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `role` varchar(255) NOT NULL DEFAULT 'teacher',
  `phone_number` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `ec_number` varchar(255) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_no` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `dob`, `employee_id`, `name`, `email`, `national_id`, `base_salary`, `role`, `phone_number`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `ec_number`, `bank_name`, `bank_account_no`) VALUES
(5, '2004-09-24', '12', 'Musa Elias Mukahlera', 'musamukahlera@gmail.com', '22042235E22', 0.00, 'admin', '0787247792', NULL, '$2y$12$Y73mf57FNoxkJKNpl3Yoj.4gzMrhdXl3OsfYHM/fdluy0XHbbbmzm', NULL, '2026-01-27 19:44:44', '2026-02-27 11:24:23', 'admin', NULL, NULL),
(12, '2001-11-11', '222042244E22', 'Wilson Mafuriranwa', 'wilson@eac.co.zw', '202020', 0.00, 'teacher', NULL, NULL, '$2y$12$dj1PRzPN1Kfbg6QqjSZrBuuAm0sOzHHkSkArp5jq7WpmrsyZ8iK9u', NULL, '2026-02-09 11:28:38', '2026-09-06 16:42:28', '1234', NULL, NULL),
(13, '2004-09-27', 'eac15', 'Takudzwa Masiwa', 'tmasiwa@eac.ac.zw', '632580686B44', 0.00, 'teacher', '0712070344', NULL, '$2y$12$NDz87.YcOZeu7VJA5qWkde8QaKBG6aYXCvNZjCcQxc5TryNAkym3S', NULL, '2026-02-11 07:02:21', '2026-03-04 18:13:02', '1212', NULL, NULL),
(16, NULL, NULL, 'Sailas Benza', 'st20266885@school.com', NULL, 0.00, 'student', NULL, NULL, '$2y$12$uzW4ZGUxSwdg2sUMkriSLeYbmHdLo8XB0lZZzF9fhSl310p3QYkpi', NULL, '2026-02-12 16:15:20', '2026-02-12 16:15:20', NULL, NULL, NULL),
(17, NULL, NULL, 'Neville Mupasa', 'st20261705@school.com', NULL, 0.00, 'student', NULL, NULL, '$2y$12$PQQ04xKZfLr5Z0/ehjsu7eM7Fw/aJ4/YZrxI5D9Ws.bDB4H9RRpju', NULL, '2026-02-12 16:38:35', '2026-02-12 16:38:35', NULL, NULL, NULL),
(21, '2005-09-24', NULL, 'Hamamunashe Tirekerwi', 'hamamunashe@gmail.com', '222042235P22', 0.00, 'receptionist', '+263787247792', NULL, '$2y$12$F0gMKvMTihliQWQ/0E8kFuUou0R1R.yUi/fIIKg8Pe9ABtcpnp7FO', NULL, '2026-02-25 16:17:39', '2026-09-06 15:31:29', 'clerk', NULL, NULL),
(30, NULL, NULL, 'Musa Elias Mukahlera', 'mmukahlera1@student.local', '222042235E22', 0.00, 'student', NULL, NULL, '$2y$12$OtC7.NiVdWvzNygxssAcSu6lPFKkKCFNjywx/IYdJTomm.fqZc/DW', NULL, '2026-09-03 11:30:26', '2026-09-03 11:30:26', '222042235E22', NULL, NULL),
(31, NULL, NULL, 'Hamamunashe Tirekerwi', 'htirekerwi2@student.local', '12121212D12', 0.00, 'student', NULL, NULL, '$2y$12$nN4FRi2VKy467dsw8V0EdOvgApjR0ZvmdZ2zsnWKKWF65XNXjrDN2', NULL, '2026-09-03 11:33:58', '2026-09-03 11:33:58', '12121212D12', NULL, NULL),
(32, NULL, NULL, 'Munashe Mbaimbai', 'mmbaimbai3@student.local', '112233445566778899P77', 0.00, 'student', NULL, NULL, '$2y$12$Xp8x/nGht8jHrSuCiCSzTeu2SMWeRvqP4B9SB/iKG2evgHcns/Zoy', NULL, '2026-09-04 06:57:36', '2026-09-04 06:57:36', '112233445566778899P77', NULL, NULL),
(33, NULL, NULL, 'John Doe', 'jdoe4@student.local', '18621122Q11', 0.00, 'student', NULL, NULL, '$2y$12$Y1JznIJGRGrwxNAnxl9sb.ck2r0dMb2RWRBYCgy98d/aLVDGD8wXe', NULL, '2026-09-04 07:01:01', '2026-09-04 07:01:01', '18621122Q11', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject` (`subject_type`,`subject_id`),
  ADD KEY `causer` (`causer_type`,`causer_id`),
  ADD KEY `activity_log_log_name_index` (`log_name`);

--
-- Indexes for table `activity_marks`
--
ALTER TABLE `activity_marks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `activity_marks_unique` (`class_activity_id`,`student_id`),
  ADD KEY `activity_marks_student_id_index` (`student_id`);

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admissions_tracking_id_unique` (`tracking_id`),
  ADD UNIQUE KEY `admissions_identity_number_unique` (`identity_number`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `class_activities`
--
ALTER TABLE `class_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `class_activities_term_id_foreign` (`term_id`),
  ADD KEY `class_activities_created_by_foreign` (`created_by`),
  ADD KEY `class_activities_lookup_idx` (`subject_assignment_id`,`term_id`,`activity_date`);

--
-- Indexes for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_school_class` (`school_class_id`),
  ADD KEY `fk_subject` (`subject_id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exams_term_id_foreign` (`term_id`),
  ADD KEY `exams_subject_assignment_id_foreign` (`subject_assignment_id`),
  ADD KEY `exams_class_id_foreign` (`class_id`);

--
-- Indexes for table `exam_attendances`
--
ALTER TABLE `exam_attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `exam_attendances_exam_id_student_id_unique` (`exam_id`,`student_id`),
  ADD KEY `exam_attendances_student_id_foreign` (`student_id`),
  ADD KEY `exam_attendances_verified_by_foreign` (`verified_by`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_structures_term_id_foreign` (`term_id`),
  ADD KEY `fee_structures_student_id_foreign` (`student_id`);

--
-- Indexes for table `fee_transactions`
--
ALTER TABLE `fee_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fee_transactions_student_id_foreign` (`student_id`),
  ADD KEY `fee_transactions_term_id_foreign` (`term_id`),
  ADD KEY `fee_transactions_payment_id_foreign` (`payment_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inventory_items_sku_unique` (`sku`);

--
-- Indexes for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inventory_stocks_inventory_item_id_foreign` (`inventory_item_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `marks`
--
ALTER TABLE `marks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `marks_exam_id_foreign` (`exam_id`),
  ADD KEY `marks_student_id_foreign` (`student_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_student_id_foreign` (`student_id`),
  ADD KEY `payments_term_id_foreign` (`term_id`),
  ADD KEY `payments_received_by_foreign` (`received_by`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payslips_user_id_foreign` (`user_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `school_classes`
--
ALTER TABLE `school_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `school_classes_class_code_unique` (`class_code`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `students_student_number_unique` (`student_number`),
  ADD KEY `students_enrollment_term_id_foreign` (`enrollment_term_id`),
  ADD KEY `students_class_id_foreign` (`class_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subjects_subject_code_unique` (`subject_code`);

--
-- Indexes for table `subject_assignments`
--
ALTER TABLE `subject_assignments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacher_subject_class_unique` (`teacher_id`,`subject_id`,`class_id`),
  ADD KEY `subject_assignments_subject_id_foreign` (`subject_id`),
  ADD KEY `subject_assignments_class_id_foreign` (`class_id`);

--
-- Indexes for table `terms`
--
ALTER TABLE `terms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`id`),
  ADD KEY `timetables_class_id_foreign` (`class_id`),
  ADD KEY `timetables_subject_id_foreign` (`subject_id`),
  ADD KEY `timetables_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_ec_number_unique` (`ec_number`),
  ADD UNIQUE KEY `users_employee_id_unique` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `activity_marks`
--
ALTER TABLE `activity_marks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `class_activities`
--
ALTER TABLE `class_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_subject`
--
ALTER TABLE `class_subject`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `exam_attendances`
--
ALTER TABLE `exam_attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `fee_transactions`
--
ALTER TABLE `fee_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `marks`
--
ALTER TABLE `marks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `school_classes`
--
ALTER TABLE `school_classes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=179;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subject_assignments`
--
ALTER TABLE `subject_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `terms`
--
ALTER TABLE `terms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_marks`
--
ALTER TABLE `activity_marks`
  ADD CONSTRAINT `activity_marks_class_activity_id_foreign` FOREIGN KEY (`class_activity_id`) REFERENCES `class_activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `activity_marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_activities`
--
ALTER TABLE `class_activities`
  ADD CONSTRAINT `class_activities_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `class_activities_subject_assignment_id_foreign` FOREIGN KEY (`subject_assignment_id`) REFERENCES `subject_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_activities_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `class_subject`
--
ALTER TABLE `class_subject`
  ADD CONSTRAINT `fk_school_class` FOREIGN KEY (`school_class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `exams_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_subject_assignment_id_foreign` FOREIGN KEY (`subject_assignment_id`) REFERENCES `subject_assignments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exams_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `exam_attendances`
--
ALTER TABLE `exam_attendances`
  ADD CONSTRAINT `exam_attendances_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_attendances_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_attendances_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fee_structures`
--
ALTER TABLE `fee_structures`
  ADD CONSTRAINT `fee_structures_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_structures_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `fee_transactions`
--
ALTER TABLE `fee_transactions`
  ADD CONSTRAINT `fee_transactions_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fee_transactions_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fee_transactions_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `inventory_stocks`
--
ALTER TABLE `inventory_stocks`
  ADD CONSTRAINT `inventory_stocks_inventory_item_id_foreign` FOREIGN KEY (`inventory_item_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marks`
--
ALTER TABLE `marks`
  ADD CONSTRAINT `marks_exam_id_foreign` FOREIGN KEY (`exam_id`) REFERENCES `exams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `marks_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_received_by_foreign` FOREIGN KEY (`received_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `payments_term_id_foreign` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `students_enrollment_term_id_foreign` FOREIGN KEY (`enrollment_term_id`) REFERENCES `terms` (`id`);

--
-- Constraints for table `subject_assignments`
--
ALTER TABLE `subject_assignments`
  ADD CONSTRAINT `subject_assignments_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_assignments_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subject_assignments_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `timetables`
--
ALTER TABLE `timetables`
  ADD CONSTRAINT `timetables_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `school_classes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetables_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `timetables_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
