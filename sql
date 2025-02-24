-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 24, 2024 at 08:06 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `dbms_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai`
--

DROP TABLE IF EXISTS `ai`;
CREATE TABLE IF NOT EXISTS `ai` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_id` varchar(255) DEFAULT NULL,
  `course_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `priority` int DEFAULT NULL,
  `course_code` varchar(255) NOT NULL,
  `teacher_username` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ai_courses`
--

DROP TABLE IF EXISTS `ai_courses`;
CREATE TABLE IF NOT EXISTS `ai_courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(50) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `dept` varchar(50) DEFAULT 'AI',
  `year` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `ai_courses`
--

INSERT INTO `ai_courses` (`id`, `course_code`, `course_name`, `credits`, `dept`, `year`) VALUES
(12, '21AIE499', 'Project Phase - 2', 10, 'AI', NULL),
(13, '22AIE311', 'Software Engineering (Project Based)', 3, 'AI', NULL),
(14, '22AIE312', 'Big Data Analytics', 3, 'AI', NULL),
(15, '22AIE313', 'Computer Vision and Image Processing', 4, 'AI', NULL),
(16, '22AIE314', 'Computer Security', 3, 'AI', NULL),
(17, '22AIE315', 'Natural Language Processing', 3, 'AI', NULL),
(18, '22AIE211', 'Introduction to Communication and IoT', 3, 'AI', NULL),
(19, '22AIE212', 'Design and Analysis of Algorithms', 3, 'AI', NULL),
(21, '22AIE213', 'Introduction to AI Robotics', 3, 'AI', NULL),
(22, '22AIE111', 'Object Oriented Programming in Java', 4, 'AI', NULL),
(23, '22AIE112', 'Data Structures and Algorithms - 1 in Java', 4, 'AI', NULL),
(24, '22AIE113', 'Elements of Computing Systems - 2', 3, 'AI', NULL),
(25, '22AIE114', 'Introduction to Electrical and Electronics Engineering', 3, 'AI', NULL),
(26, '22AIE115', 'User Interface Design', 3, 'AI', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `allocated_labs`
--

DROP TABLE IF EXISTS `allocated_labs`;
CREATE TABLE IF NOT EXISTS `allocated_labs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_username` varchar(50) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_name` varchar(255) DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `year` int DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `allocated_labs`
--

INSERT INTO `allocated_labs` (`id`, `teacher_username`, `course_code`, `course_name`, `credits`, `year`, `section`) VALUES
(1, 'sreevidhya', 'wdedw', 'Lab Name', 3, 2024, 'a'),
(2, 'sreevidhya', 'mat', 'Lab Name', 3, 2024, 'a'),
(3, 'sreevidhya', 'wdedw', 'Lab Name', 3, 2024, 'a');

-- --------------------------------------------------------

--
-- Table structure for table `allocation_status`
--

DROP TABLE IF EXISTS `allocation_status`;
CREATE TABLE IF NOT EXISTS `allocation_status` (
  `id` int NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `allocation_status`
--

INSERT INTO `allocation_status` (`id`, `published`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `assigned_courses`
--

DROP TABLE IF EXISTS `assigned_courses`;
CREATE TABLE IF NOT EXISTS `assigned_courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_username` varchar(50) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `credits` int NOT NULL,
  `year` int NOT NULL,
  `section` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=56 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `assigned_courses`
--

INSERT INTO `assigned_courses` (`id`, `teacher_username`, `course_code`, `course_name`, `credits`, `year`, `section`) VALUES
(55, 'sreevidhya', '23CSE211', 'Design and Analysis of Algorithms', 4, 2, 'b'),
(54, 'sreevidhya', '23CSE211', 'Design and Analysis of Algorithms', 4, 2, 'a'),
(53, 'sreevidhya', '19CSE499', 'Project - Phase - 2', 10, 4, 'a'),
(52, 'sreevidhya', '23CSE211', 'Design and Analysis of Algorithms', 4, 2, 'a'),
(51, 'sreevidhya', '23CSE111', 'Object Oriented Programming', 4, 1, 'b');

-- --------------------------------------------------------

--
-- Table structure for table `cs`
--

DROP TABLE IF EXISTS `cs`;
CREATE TABLE IF NOT EXISTS `cs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_id` varchar(255) DEFAULT NULL,
  `course_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `priority` int DEFAULT NULL,
  `teacher_username` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `course_code` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cs`
--

INSERT INTO `cs` (`id`, `teacher_id`, `course_name`, `priority`, `teacher_username`, `course_code`) VALUES
(3, '2', 'mat', 1, 'sreevidhya', 'mat');

-- --------------------------------------------------------

--
-- Table structure for table `cs_courses`
--

DROP TABLE IF EXISTS `cs_courses`;
CREATE TABLE IF NOT EXISTS `cs_courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(50) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `dept` varchar(50) DEFAULT 'CS',
  `year` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cs_courses`
--

INSERT INTO `cs_courses` (`id`, `course_code`, `course_name`, `credits`, `dept`, `year`) VALUES
(29, '19CSE314', 'Software Engineering', 3, 'CS', 3),
(28, '19CSE499', 'Project - Phase - 2', 10, 'CS', 4),
(30, '19CSE313', 'Principles of Programming Languages', 3, 'CS', 3),
(31, '19CSE312', 'Distributed Systems', 4, 'CS', 3),
(32, '19CSE311', 'Computer Security', 3, 'CS', 3),
(33, '23CSE211', 'Design and Analysis of Algorithms', 4, 'CS', 2),
(34, '23CSE212', 'Principle of Fundamental Languages', 3, 'CS', 2),
(35, '23CSE213', 'Computer Organization and Architecture', 4, 'CS', 2),
(36, '23CSE214', 'Operating Systems', 4, 'CS', 2),
(37, '23CSE111', 'Object Oriented Programming', 4, 'CS', 1),
(42, '23CSE113', 'User Interface Design', 3, 'CS', 1);

-- --------------------------------------------------------

--
-- Table structure for table `electives`
--

DROP TABLE IF EXISTS `electives`;
CREATE TABLE IF NOT EXISTS `electives` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_id` varchar(255) DEFAULT NULL,
  `course_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `priority` int DEFAULT NULL,
  `course_code` varchar(255) NOT NULL,
  `teacher_username` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `electives_courses`
--

DROP TABLE IF EXISTS `electives_courses`;
CREATE TABLE IF NOT EXISTS `electives_courses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(50) DEFAULT NULL,
  `course_name` varchar(100) DEFAULT NULL,
  `credits` int DEFAULT NULL,
  `dept` varchar(50) DEFAULT 'Elective',
  `year` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_code` (`course_code`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `electives_courses`
--

INSERT INTO `electives_courses` (`id`, `course_code`, `course_name`, `credits`, `dept`, `year`) VALUES
(1, 'blockchain', 'blockchain', 3, 'Elective', NULL),
(2, 'python', 'python', 2, 'Elective', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

DROP TABLE IF EXISTS `labs`;
CREATE TABLE IF NOT EXISTS `labs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_name` varchar(255) NOT NULL,
  `teachers_required` int NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `credits` int DEFAULT NULL,
  `lab_component` varchar(50) DEFAULT NULL,
  `year` int NOT NULL,
  `department` enum('cs','ai','elective','Fully Lab-Oriented') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`id`, `lab_name`, `teachers_required`, `course_code`, `credits`, `lab_component`, `year`, `department`) VALUES
(14, 'test', 2, 'wdedw', 2, '1', 1, 'cs'),
(13, 'test', 1, 'mat', 1, '1', 1, 'Fully Lab-Oriented');

-- --------------------------------------------------------

--
-- Table structure for table `lab_sections`
--

DROP TABLE IF EXISTS `lab_sections`;
CREATE TABLE IF NOT EXISTS `lab_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lab_id` int NOT NULL,
  `section_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lab_id` (`lab_id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `lab_sections`
--

INSERT INTO `lab_sections` (`id`, `lab_id`, `section_name`) VALUES
(31, 14, 'c'),
(30, 14, 'b'),
(29, 14, 'a'),
(28, 13, 'c'),
(27, 13, 'b'),
(26, 13, 'a');

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

DROP TABLE IF EXISTS `sections`;
CREATE TABLE IF NOT EXISTS `sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `course_code` varchar(255) DEFAULT NULL,
  `section` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_code` (`course_code`(250))
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`id`, `course_code`, `section`) VALUES
(6, '23CSE113', 'c'),
(5, '23CSE113', 'b'),
(4, '23CSE113', 'a'),
(7, '23CSE111', 'a'),
(8, '23CSE111', 'b'),
(9, '23CSE111', 'c'),
(10, '23CSE214', 'a'),
(11, '23CSE214', 'b'),
(12, '23CSE214', 'c'),
(13, '23CSE213', 'a'),
(14, '23CSE213', 'b'),
(15, '23CSE213', 'c'),
(16, '23CSE212', 'a'),
(17, '23CSE212', 'b'),
(18, '23CSE212', 'c'),
(19, '23CSE211', 'a'),
(20, '23CSE211', 'b'),
(21, '23CSE211', 'c'),
(22, '19CSE311', 'a'),
(23, '19CSE311', 'b'),
(24, '19CSE311', 'c'),
(25, '19CSE312', 'a'),
(26, '19CSE312', 'b'),
(27, '19CSE312', 'c'),
(28, '19CSE313', 'a'),
(29, '19CSE313', 'b'),
(30, '19CSE313', 'c'),
(31, '19CSE314', 'a'),
(32, '19CSE314', 'b'),
(33, '19CSE314', 'c'),
(34, '19CSE499', 'a'),
(35, '19CSE499', 'b'),
(36, '19CSE499', 'c'),
(37, 'test', 'a'),
(38, 'test', 'b');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
CREATE TABLE IF NOT EXISTS `teachers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('hod','cs','ai') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `email` varchar(255) NOT NULL,
  `working_hours` int DEFAULT '0',
  `available_hours` int DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `username`, `password`, `role`, `created_at`, `email`, `working_hours`, `available_hours`) VALUES
(1, 'hod', 'hod', 'hod', '2024-09-25 15:57:11', 'hod@amrita.edu', 8, 4),
(2, 'sreevidhya', 'sreevidhya', 'cs', '2024-09-25 15:57:11', 'sreevidhya@amrita.edu', 16, 7),
(3, 'rajesh', 'rajesh', 'ai', '2024-09-25 15:57:11', 'rajesh@amrita.edu', 12, 2);

-- --------------------------------------------------------

--
-- Table structure for table `teacher_lab_selections`
--

DROP TABLE IF EXISTS `teacher_lab_selections`;
CREATE TABLE IF NOT EXISTS `teacher_lab_selections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_username` varchar(50) NOT NULL,
  `course_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `priority` int NOT NULL,
  `department` enum('cs','ai','elective','Fully Lab-Oriented') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teacher_username` (`teacher_username`,`course_code`)
) ENGINE=MyISAM AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teacher_lab_selections`
--

INSERT INTO `teacher_lab_selections` (`id`, `teacher_username`, `course_code`, `priority`, `department`) VALUES
(52, 'sreevidhya', 'mat', 2, 'cs'),
(49, 'sreevidhya1', 'wdedw', 1, 'cs'),
(50, 'sreevidhya1', 'mat', 2, 'cs');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_selections`
--

DROP TABLE IF EXISTS `teacher_selections`;
CREATE TABLE IF NOT EXISTS `teacher_selections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `teacher_username` varchar(50) DEFAULT NULL,
  `course_code` varchar(20) DEFAULT NULL,
  `priority` int DEFAULT NULL,
  `department` enum('ai','elective','cs') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=253 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `teacher_selections`
--

INSERT INTO `teacher_selections` (`id`, `teacher_username`, `course_code`, `priority`, `department`) VALUES
(252, 'rajesh', 'python', 2, 'elective'),
(251, 'rajesh', 'blockchain', 1, 'elective'),
(250, 'rajesh', 'python', 0, 'elective'),
(249, 'rajesh', 'blockchain', 0, 'elective'),
(248, 'rajesh', 'python', 2, 'elective'),
(247, 'rajesh', 'blockchain', 1, 'elective'),
(246, 'rajesh', 'python', 2, 'elective'),
(245, 'rajesh', 'blockchain', 1, 'elective'),
(244, 'rajesh', 'python', 0, 'elective'),
(243, 'rajesh', 'blockchain', 0, 'elective'),
(242, 'sreevidhya', '23CSE212', 1, 'cs');

--
-- Triggers `teacher_selections`
--
DROP TRIGGER IF EXISTS `set_department_before_insert`;
DELIMITER $$
CREATE TRIGGER `set_department_before_insert` BEFORE INSERT ON `teacher_selections` FOR EACH ROW BEGIN
    -- Check if the course code exists in cs_courses
    IF EXISTS (SELECT 1 FROM cs_courses WHERE course_code = NEW.course_code) THEN
        SET NEW.department = 'cs';
    -- Check if the course code exists in ai_courses
    ELSEIF EXISTS (SELECT 1 FROM ai_courses WHERE course_code = NEW.course_code) THEN
        SET NEW.department = 'ai';
    -- If it doesn't match either, set department to elective
    ELSE
        SET NEW.department = 'elective';
    END IF;
END
$$
DELIMITER ;
COMMIT;
