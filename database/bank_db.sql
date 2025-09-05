-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 27, 2025 at 09:49 AM
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
-- Database: `bank_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `term` int(11) NOT NULL,
  `interest_rate` decimal(5,2) DEFAULT 5.00,
  `status` enum('pending','approved','rejected','paid') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `user_id`, `amount`, `purpose`, `term`, `interest_rate`, `status`, `created_at`) VALUES
(1, 1, 1000.00, NULL, 12, 5.00, 'approved', '2025-08-22 15:23:08'),
(2, 1, 1000.00, NULL, 12, 5.00, 'approved', '2025-08-22 15:24:27'),
(3, 1, 10000.00, 'emergency', 0, 5.00, 'paid', '2025-08-22 15:28:52'),
(4, 1, 10000.00, NULL, 0, 5.00, 'paid', '2025-08-22 15:55:59'),
(5, 1, 1000.00, NULL, 0, 5.00, 'approved', '2025-08-22 15:59:54'),
(6, 1, 1000.00, NULL, 0, 5.00, 'approved', '2025-08-22 16:04:15'),
(7, 1, 3000.00, NULL, 0, 5.00, 'paid', '2025-08-22 16:08:14'),
(8, 1, 3000.00, NULL, 0, 5.00, 'paid', '2025-08-22 16:08:18'),
(9, 1, 3000.00, NULL, 0, 5.00, 'paid', '2025-08-22 16:11:33'),
(10, 1, 10000.00, NULL, 0, 5.00, 'paid', '2025-08-22 16:16:40'),
(11, 1, 1000.00, NULL, 3, 5.00, 'paid', '2025-08-22 16:58:28'),
(12, 1, 10000.00, NULL, 3, 5.00, 'paid', '2025-08-22 16:58:39'),
(13, 1, 1000.00, NULL, 3, 5.00, 'approved', '2025-08-22 17:35:46'),
(14, 1, 1000.00, NULL, 3, 5.00, 'approved', '2025-08-22 17:48:48'),
(15, 1, 1000.00, NULL, 3, 5.00, 'paid', '2025-08-22 17:52:38'),
(16, 2, 10000.00, NULL, 12, 5.00, 'paid', '2025-08-27 07:40:16');

-- --------------------------------------------------------

--
-- Table structure for table `loan_repayments`
--

CREATE TABLE `loan_repayments` (
  `id` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdraw','loan repayment') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` enum('success','failed') DEFAULT 'success',
  `date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `status`, `date`, `created_at`) VALUES
(1, 1, 'deposit', 1000.00, 'success', '2025-08-22 07:22:57', '2025-08-22 14:14:36'),
(2, 1, 'withdraw', -100.00, 'success', '2025-08-22 07:23:02', '2025-08-22 14:14:36'),
(3, 1, 'withdraw', -1000.00, 'success', '2025-08-22 07:35:08', '2025-08-22 14:14:36'),
(4, 1, 'deposit', 100.00, 'success', '2025-08-22 07:45:32', '2025-08-22 14:14:36'),
(5, 1, 'withdraw', -100.00, 'success', '2025-08-22 07:45:40', '2025-08-22 14:14:36'),
(6, 1, 'deposit', 10000.00, 'success', '2025-08-22 08:17:49', '2025-08-22 14:17:49'),
(7, 1, 'withdraw', -400.00, 'success', '2025-08-22 08:17:55', '2025-08-22 14:17:55'),
(8, 1, 'deposit', 10000.00, 'success', '2025-08-22 15:28:52', '2025-08-22 15:28:52'),
(9, 1, 'deposit', 10000.00, 'success', '2025-08-22 15:46:18', '2025-08-22 15:46:18'),
(10, 1, 'withdraw', 10000.00, 'success', '2025-08-22 15:46:32', '2025-08-22 15:46:32'),
(11, 1, 'deposit', 1000.00, 'success', '2025-08-22 15:46:38', '2025-08-22 15:46:38'),
(12, 1, '', 10000.00, 'success', '2025-08-22 15:55:59', '2025-08-22 15:55:59'),
(13, 1, '', 1000.00, 'success', '2025-08-22 15:59:54', '2025-08-22 15:59:54'),
(14, 1, '', 1000.00, 'success', '2025-08-22 16:04:15', '2025-08-22 16:04:15'),
(15, 1, '', 3000.00, 'success', '2025-08-22 16:08:14', '2025-08-22 16:08:14'),
(16, 1, '', 3000.00, 'success', '2025-08-22 16:08:18', '2025-08-22 16:08:18'),
(17, 1, '', 3000.00, 'success', '2025-08-22 16:11:33', '2025-08-22 16:11:33'),
(18, 1, 'withdraw', 32000.00, 'success', '2025-08-22 16:11:44', '2025-08-22 16:11:44'),
(19, 1, 'deposit', 32000.00, 'success', '2025-08-22 16:11:53', '2025-08-22 16:11:53'),
(20, 1, '', 10000.00, 'success', '2025-08-22 16:16:40', '2025-08-22 16:16:40'),
(21, 1, '', 1000.00, 'success', '2025-08-22 16:58:28', '2025-08-22 16:58:28'),
(22, 1, '', 10000.00, 'success', '2025-08-22 16:58:39', '2025-08-22 16:58:39'),
(23, 1, 'withdraw', 1000.00, 'success', '2025-08-22 17:02:48', '2025-08-22 17:02:48'),
(24, 1, 'withdraw', 1000.00, 'success', '2025-08-22 17:03:07', '2025-08-22 17:03:07'),
(25, 1, 'withdraw', -1000.00, 'success', '2025-08-22 17:05:20', '2025-08-22 17:05:20'),
(26, 1, 'deposit', 10000.00, 'success', '2025-08-22 17:35:29', '2025-08-22 17:35:29'),
(27, 1, '', 1000.00, 'success', '2025-08-22 17:35:46', '2025-08-22 17:35:46'),
(28, 1, '', 1000.00, 'success', '2025-08-22 17:48:48', '2025-08-22 17:48:48'),
(29, 1, '', 1000.00, 'success', '2025-08-22 17:52:38', '2025-08-22 17:52:38'),
(30, 1, 'deposit', 1000.00, 'success', '2025-08-22 18:07:21', '2025-08-22 18:07:21'),
(31, 1, 'withdraw', -1000.00, 'success', '2025-08-22 18:07:27', '2025-08-22 18:07:27'),
(32, 1, '', 1000.00, 'success', '2025-08-22 18:08:26', '2025-08-22 18:08:26'),
(33, 2, 'deposit', 20000.00, 'success', '2025-08-27 07:39:34', '2025-08-27 07:39:34'),
(34, 2, 'withdraw', 10000.00, 'success', '2025-08-27 07:39:50', '2025-08-27 07:39:50'),
(35, 2, 'loan repayment', 10000.00, 'success', '2025-08-27 07:40:20', '2025-08-27 07:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `balance` decimal(10,2) DEFAULT 0.00,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `created_at`, `balance`, `phone`, `address`) VALUES
(1, 'Nino Emmanuel Tadeo', 'Nino', 'ninoemmanueltadeo@gmail.com', '$2y$10$rYekj2Ue3nvT6rjpbAuZKumb3DInxdfAzb8FBILcRZKE.tJBCdzgG', '2025-08-21 12:12:03', 12067.00, '09612367677', 'Brgy. Licaong Science City of Munoz Nueva Ecija'),
(2, 'Joseph Matthew Ringor', 'Silent7Stars', 'josephmatthewringor@gmail.com', '$2y$10$VeQvu31zJhDX./6NZVa8zuZxKnPnLhj/nziXO8GkKqrTIBlsWpYqq', '2025-08-27 07:39:19', 10000.00, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `loan_repayments`
--
ALTER TABLE `loan_repayments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `loan_id` (`loan_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `loan_repayments`
--
ALTER TABLE `loan_repayments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `loan_repayments`
--
ALTER TABLE `loan_repayments`
  ADD CONSTRAINT `loan_repayments_ibfk_1` FOREIGN KEY (`loan_id`) REFERENCES `loans` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
