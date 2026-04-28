-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 08:26 PM
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
-- Database: `umbc_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `umbc_accounts`
--

CREATE TABLE `umbc_accounts` (
  `umbc_id` varchar(60) NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `umbc_email` varchar(100) NOT NULL,
  `pword_hash` varchar(320) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `umbc_accounts`
--

INSERT INTO `umbc_accounts` (`umbc_id`, `first_name`, `last_name`, `umbc_email`, `pword_hash`) VALUES
('AR36062', 'Avi', 'Hill', 'ahill@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('BD30033', 'Sophia', 'Carter', 'scarter@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('BZ51347', 'Molemo', 'Anderson', 'manderson@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('CM22676', 'Elvis', 'Miller', 'emiller@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('CT93227', 'Lily', 'Adams', 'ladams@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('DA46048', 'Abe', 'Green', 'agreen@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('DV80381', 'Zoya', 'Campbell', 'zcampbell@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('EI28301', 'Rania', 'Robinson', 'rrobinson@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('FZ99192', 'Susanna', 'Green', 'sgreen@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('GH76237', 'Aren', 'Garcia', 'agarcia@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('GW19116', 'Javan', 'Torres', 'jtorres@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('GY54118', 'Dani', 'Martinez', 'dmartinez@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('HD59823', 'Kaila', 'Garcia', 'kgarcia@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('HE23434', 'Adamo', 'Thomas', 'athomas@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('HO87236', 'Bridget', 'Clark', 'bclark@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('IC37653', 'Molly', 'Sanchez', 'msanchez@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('IW99593', 'Lillian', 'Nelson', 'lnelson@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('KY17331', 'Michael', 'Baker', 'mbaker@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('LL37460', 'Kodi', 'Hernandez', 'khernandez@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('LO89179', 'Samuel', 'Sudhakar', 'samuels4@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('LT44671', 'Himani', 'Thompson', 'hthompson@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('MC82357', 'Ini', 'Davis', 'idavis@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('ML38746', 'Shrikant', 'Nguyen', 'snguyen@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('NB13905', 'Angela', 'Nguyen', 'anguyen@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('NK46421', 'Chiara', 'Hall', 'chall@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('OI33374', 'Sam', 'Dolle', 'sdolle2@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('OM45382', 'Max A.', 'Hernandez', 'mhernandez1@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('OU57819', 'Karen', 'Moore', 'kmoore@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('PM94259', 'O\'Tega', 'Adams', 'oadams@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('QP21915', 'Sina', 'Rodriguez', 'srodriguez@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('RA99166', 'Zach', 'Taylor', 'ztaylor@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('RX42087', 'Manav', 'Hernandez', 'mhernandez@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('TC60432', 'Theo', 'Ramirez', 'tramirez@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('TO79352', 'Tristan', 'Harris', 'tharris@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('UT57400', 'Iniya', 'Jackson', 'ijackson@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('UW81426', 'Aya', 'Rivera', 'arivera@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('VH47930', 'Joseph', 'Williams', 'jwilliams@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('WB55131', 'Justin', 'Collier', 'justinc4@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('WK37869', 'Moyo', 'Torres', 'mtorres@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('WR38785', 'Max M.', 'Green', 'mgreen@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('XO80284', 'Ian', 'Williams', 'iwilliams@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('XR21395', 'Akanksha', 'Baker', 'abaker@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('XR80644', 'Shakib', 'Thomas', 'sthomas@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('XS66155', 'Shayan', 'Taylor', 'staylor@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8'),
('ZA30926', 'Chelsea', 'Moore', 'cmoore@umbc.edu', '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8');

-- --------------------------------------------------------

--
-- Table structure for table `umbc_courses`
--

CREATE TABLE `umbc_courses` (
  `course_id` int(11) NOT NULL,
  `course_subject` varchar(8) NOT NULL,
  `course_code` varchar(8) NOT NULL,
  `course_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `umbc_courses`
--

INSERT INTO `umbc_courses` (`course_id`, `course_subject`, `course_code`, `course_name`) VALUES
(10000, 'BIOL', '101', 'Concepts of Biology'),
(10001, 'BIOL', '140', 'Foundations of Biology: Ecology and Evolution'),
(10002, 'BIOL', '141', 'Foundations of Biology: Cells, Energy and Organisms'),
(10003, 'BIOL', '251', 'Human Anatomy and Physiology I'),
(10004, 'BIOL', '273', 'Microbiology for Allied Health'),
(10005, 'BIOL', '302', 'Molecular and General Genetics'),
(10006, 'BIOL', '303', 'Cell Biology'),
(10007, 'BIOL', '430', 'Biological Chemistry'),
(10008, 'CHEM', '100', 'The Chemical World'),
(10009, 'CHEM', '101', 'Principles of Chemistry I'),
(10010, 'CHEM', '102', 'Principles of Chemistry II'),
(10011, 'CHEM', '120', 'Introduction to General, Organic, and Biochemistry'),
(10012, 'CHEM', '300', 'Analytical Chemistry'),
(10013, 'CHEM', '303', 'Physical Chemistry for the Biochemical Sciences'),
(10014, 'CHEM', '351', 'Organic Chemistry I'),
(10015, 'CHEM', '352', 'Organic Chemistry II'),
(10016, 'CMPE', '212', 'Principles of Digital Design'),
(10017, 'CMPE', '306', 'Introductory Circuit Theory'),
(10018, 'CMPE', '314', 'Principles of Electronic Circuits'),
(10019, 'CMSC', '201', 'Computer Science I'),
(10020, 'CMSC', '202', 'Computer Science II'),
(10021, 'CMSC', '203', 'Discrete Structures'),
(10022, 'CMSC', '313', 'Computer Organization and Assembly Language Programming'),
(10023, 'CMSC', '331', 'Principles of Programming Language'),
(10024, 'CMSC', '341', 'Data Structures'),
(10025, 'CMSC', '411', 'Computer Architecture'),
(10026, 'CMSC', '421', 'Principles of Operating Systems'),
(10027, 'CMSC', '426', 'Principles of Computer Security'),
(10028, 'CMSC', '441', 'Design and Analysis of Algorithms'),
(10029, 'CMSC', '447', 'Software Engineering I'),
(10030, 'CMSC', '471', 'Introduction to Artificial Intelligence'),
(10031, 'ECON', '101', 'Principles of Microeconomics'),
(10032, 'ECON', '102', 'Principles of Macroeconomics'),
(10033, 'GES', '102', 'People, Place, and Environment: A Global Geographic Perspective'),
(10034, 'GES', '110', 'The Changing Earth: Climate, Ecosystems, Water, and Landscapes'),
(10035, 'GES', '120', 'Environmental Science and Conservation'),
(10036, 'IS', '147', 'Introduction to Computer Programming'),
(10037, 'IS', '247', 'Computer Programming II'),
(10038, 'IS', '300', 'Management Information Systems'),
(10039, 'IS', '310', 'Software and Hardware Concepts'),
(10040, 'MATH', '104', 'Quantitative Literacy'),
(10041, 'MATH', '106', 'Algebra and Elementary Functions'),
(10042, 'MATH', '150', 'Precalculus Mathematics'),
(10043, 'MATH', '151', 'Calculus and Analytic Geometry I'),
(10044, 'MATH', '152', 'Calculus and Analytic Geometry II'),
(10045, 'MATH', '155', 'Applied Calculus'),
(10046, 'MATH', '215', 'Applied Finite Mathematics'),
(10047, 'MATH', '225', 'Introduction to Differential Equations'),
(10048, 'MATH', '251', 'Multivariable Calculus'),
(10049, 'MATH', '300', 'Introduction to Mathematical Reasoning'),
(10050, 'MATH', '301', 'Introduction to Contemporary Mathematics'),
(10051, 'PHYS', '111', 'Basic Physics I'),
(10052, 'PHYS', '112', 'Basic Physics II'),
(10053, 'PHYS', '121', 'Introductory Physics I'),
(10054, 'PHYS', '122', 'Introductory Physics II'),
(10055, 'SCI', '100', 'Science of Water; An Interdisciplinary Study'),
(10056, 'SPAN', '101', 'Elementary Spanish I'),
(10057, 'SPAN', '102', 'Elementary Spanish II'),
(10058, 'SPAN', '201', 'Intermediate Spanish I'),
(10059, 'SPAN', '202', 'Intermediate Spanish II'),
(10060, 'STAT', '121', 'Introduction to Statistics for the Social Sciences'),
(10061, 'STAT', '350', 'Statistics with Applications in the Biological Sciences'),
(10062, 'STAT', '351', 'Applied Statistics for Business and Economics'),
(10063, 'STAT', '355', 'Introduction to Probability and Statistics for Scientists and Engineers');

-- --------------------------------------------------------

--
-- Table structure for table `umbc_subjects`
--

CREATE TABLE `umbc_subjects` (
  `subject_code` varchar(8) NOT NULL,
  `subject_name` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `umbc_subjects`
--

INSERT INTO `umbc_subjects` (`subject_code`, `subject_name`) VALUES
('BIOL', 'Biology'),
('CHEM', 'Chemistry'),
('CMPE', 'Computer Engineering'),
('CMSC', 'Computer Science'),
('ECON', 'Economics'),
('GES', 'Geographical and Environmental Systems'),
('IS', 'Information Systems'),
('MATH', 'Mathematics'),
('PHYS', 'Physics'),
('SCI', 'Science'),
('SPAN', 'Spanish'),
('STAT', 'Statistics');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `umbc_accounts`
--
ALTER TABLE `umbc_accounts`
  ADD PRIMARY KEY (`umbc_id`),
  ADD UNIQUE KEY `umbc_email` (`umbc_email`),
  ADD KEY `idx_first_name` (`first_name`);

--
-- Indexes for table `umbc_courses`
--
ALTER TABLE `umbc_courses`
  ADD PRIMARY KEY (`course_id`),
  ADD KEY `idx_course_subject` (`course_subject`),
  ADD KEY `idx_course_code` (`course_code`);

--
-- Indexes for table `umbc_subjects`
--
ALTER TABLE `umbc_subjects`
  ADD PRIMARY KEY (`subject_code`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `umbc_courses`
--
ALTER TABLE `umbc_courses`
  ADD CONSTRAINT `fk_course_subject` FOREIGN KEY (`course_subject`) REFERENCES `umbc_subjects` (`subject_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
