-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2026 at 11:08 AM
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
(2, 'KPC-2026-RO7VKR', '212223A24', 'Neville Mupasa', '2026-02-18', 'Form 1', '3094 Budiriro 2', 'Musa Elias Mukahlera', '+263787247792', 'musamukahlera@gmail.com', 'Masvingo Day School', 'pending', NULL, '2026-02-22 09:22:16', '2026-03-30 16:40:33', '8 Subjects', 'admissions/results/kz2Vu1xY9b4uJ9arg5HjMgaaSoNNKYdEFv6RGFVu.png', 'admissions/recommendations/Hi5vyYJF1F9kaF5kWpUb1tqyhCCQq00hLBHXIEhf.png', 'qwsedfghj'),
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

--
-- Dumping data for table `exams`
--

INSERT INTO `exams` (`id`, `exam_name`, `term_id`, `subject_id`, `exam_date`, `status`, `created_at`, `updated_at`, `subject_assignment_id`, `class_id`) VALUES
(10, 'Mid Year', 3, 6, '2026-02-11', 'pending', '2026-02-11 10:50:01', '2026-02-11 10:50:01', NULL, NULL),
(14, 'Mid Year', 2, 1, '2026-02-25', 'pending', '2026-02-25 18:56:05', '2026-02-25 18:56:05', NULL, NULL);

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
(59, 'fees', 150.00, 'Form 1', 2, '2026-02-07 15:16:25', '2026-02-07 15:16:25', NULL),
(60, 'fees', 150.00, 'Form 1', 3, '2026-02-07 15:18:18', '2026-02-07 15:18:18', NULL),
(62, 'School Fees', 160.00, 'Form 2', 3, '2026-02-09 10:54:12', '2026-02-09 10:54:12', NULL);

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
(61, '2026_08_25_125948_add_batch_uuid_column_to_activity_log_table', 44);

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
(56, 166, 2, 150.00, '2026-02-25', 'Cash', 'ny12', NULL, 'qwertyu', '2026-02-25 17:47:31', '2026-02-25 17:47:31'),
(57, 166, 3, 10.00, '2026-03-05', 'Cash', 'incentie', NULL, NULL, '2026-03-05 08:58:37', '2026-03-05 08:58:37'),
(58, 166, 3, -10.00, '2026-03-05', 'Credit Withdrawal', 'WD-69A964E4DDF65', NULL, 'Paid More Extra fees', '2026-03-05 09:11:32', '2026-03-05 09:11:32');

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
('11wNUarseKf1w340ds8OvMB171DdwqL7lLEge0Yr', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiWGpROTB3SVZLYkJOaTRCaEJRc05ZTllDalRwSG1vQWhLTjU4MU52eSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9mZWVzL2hpc3RvcnkiO3M6NToicm91dGUiO3M6MTA6ImZlZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O30=', 1788426167);

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
(166, NULL, 3, 'EAC260001', 'Musa Elias', 'Mukahlera', '2004-09-24', 'Male', '222042235E22', 'Form 1', 'active', '3094 Budiriro 2', 'Musa Elias Mukahlera', 'eac260001@eac.cac.zw', '+263787247792', '2026-02-25', 3, 'alumni', NULL, 'Musa Elias Mukahlera', '$2y$12$SwN65L4KvWiZE/lS0Fp.nOjNK5pWPWX3Mdn6OCshWqyjTTFKpwjKq', '2026-02-25 11:01:44', '2026-07-07 15:17:57', NULL, 0.00, 'biometrics/face_166_1781968841.jpg', NULL),
(167, NULL, 2, 'EAC260167', 'Nancy', 'Deke', '2004-09-24', 'Female', '152024401N15', 'Form 2', 'active', '3094 Budiriro 2\r\nOnline', 'ALFERO1', 'eac260167@eac.cac.zw', '+263787247792', '2026-06-20', 3, 'alumni', NULL, 'ALFERO1', '$2y$12$EuZBW6Qn4lZ5IFHvryQEmeBujLqwkkKOKRRr/noocWnCgZTrQ2bmq', '2026-06-20 13:01:25', '2026-07-07 15:17:57', NULL, 0.00, 'biometrics/face_167_1781968877.jpg', NULL);

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
(2, 'Term 1', '2026', '2026-01-01', '2026-02-05', 0, 'open', '2026-02-06 09:35:56', '2026-02-25 19:24:42'),
(3, 'Term 2', '2026', '2026-02-06', '2026-02-09', 1, 'open', '2026-02-06 09:36:13', '2026-02-25 19:24:42');

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
(12, '2001-11-11', '222042244E22', 'Wilson Mafuriranwa', 'wilson@eac.co.zw', '202020', 0.00, 'teacher', NULL, NULL, '$2y$12$dj1PRzPN1Kfbg6QqjSZrBuuAm0sOzHHkSkArp5jq7WpmrsyZ8iK9u', NULL, '2026-02-09 11:28:38', '2026-07-07 13:12:23', '1234', NULL, NULL),
(13, '2004-09-27', 'eac15', 'Takudzwa Masiwa', 'tmasiwa@eac.ac.zw', '632580686B44', 0.00, 'teacher', '0712070344', NULL, '$2y$12$NDz87.YcOZeu7VJA5qWkde8QaKBG6aYXCvNZjCcQxc5TryNAkym3S', NULL, '2026-02-11 07:02:21', '2026-03-04 18:13:02', '1212', NULL, NULL),
(16, NULL, NULL, 'Sailas Benza', 'st20266885@school.com', NULL, 0.00, 'student', NULL, NULL, '$2y$12$uzW4ZGUxSwdg2sUMkriSLeYbmHdLo8XB0lZZzF9fhSl310p3QYkpi', NULL, '2026-02-12 16:15:20', '2026-02-12 16:15:20', NULL, NULL, NULL),
(17, NULL, NULL, 'Neville Mupasa', 'st20261705@school.com', NULL, 0.00, 'student', NULL, NULL, '$2y$12$PQQ04xKZfLr5Z0/ehjsu7eM7Fw/aJ4/YZrxI5D9Ws.bDB4H9RRpju', NULL, '2026-02-12 16:38:35', '2026-02-12 16:38:35', NULL, NULL, NULL),
(20, NULL, NULL, 'Musa Elias Mukahlera', 'eac260001@eac.cac.zw', '222042235E22', 0.00, 'student', NULL, NULL, '$2y$12$.EdouXxzxkSvcFwTTt8Xx.L9a46u2zbFP70w6Dr6mcSDAJDIYgwFi', NULL, '2026-02-25 11:01:45', '2026-06-20 12:38:07', '222042235E22', NULL, NULL),
(21, '2005-09-24', NULL, 'Hamamunashe Tirekerwi', 'hamamunashe@gmail.com', '12121212D12', 0.00, 'receptionist', '+263787247792', NULL, '$2y$12$Q/ccXaybfEynPt6SNRZIW.rbTbYiE9fBktpY/edNv.92dTnvpPu2.', NULL, '2026-02-25 16:17:39', '2026-02-25 16:26:09', '1861', NULL, NULL),
(22, NULL, NULL, 'Nancy Deke', 'eac260167@eac.cac.zw', '152024401N15', 0.00, 'student', NULL, NULL, '$2y$12$WUQdmXc6RHAO/GRHJHBV9uighrNfCxnCZiaaw95tO.1a96b3KY9UC', NULL, '2026-06-20 13:01:25', '2026-06-20 13:01:25', '152024401N15', NULL, NULL);

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_structures`
--
ALTER TABLE `fee_structures`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `fee_transactions`
--
ALTER TABLE `fee_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

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
