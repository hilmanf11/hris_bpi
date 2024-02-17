-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Okt 2022 pada 01.31
-- Versi server: 10.4.14-MariaDB
-- Versi PHP: 7.4.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `apptmp`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `approvals`
--

CREATE TABLE `approvals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `user_approval_1` varchar(30) DEFAULT NULL,
  `user_approval_2` varchar(30) DEFAULT NULL,
  `user_approval_3` varchar(30) DEFAULT NULL,
  `user_approval_4` varchar(30) DEFAULT NULL,
  `user_approval_5` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `approvals`
--

INSERT INTO `approvals` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `table_name`, `user_approval_1`, `user_approval_2`, `user_approval_3`, `user_approval_4`, `user_approval_5`, `status`) VALUES
('603bdaf4a2e944b38c6e679ae06f53', 'admin', '2022-09-30 04:40:46', 'admin', '2022-09-30 04:40:59', 0, 'users', 'hilman', 'admin', '', '', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `config`
--

CREATE TABLE `config` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo` text DEFAULT NULL,
  `favicon` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `theme` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `config`
--

INSERT INTO `config` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `address`, `logo`, `favicon`, `image`, `theme`, `status`) VALUES
('4c9a9e62-3ff6-11ed-a526-7085c2', 'admin', '2022-10-01 06:31:13', NULL, NULL, 0, 'PIXELPHY', 'PIXELPHY INDONESIA', 'TEMPLATE SOFTWARE EASYUI', 'Jl Kampung Seni No 07, Cinunuk, Cileunyi, Kab Bandung', 'http://localhost:8080/tmp_easyui/assets/image/config/logo/1664509842.png', 'http://localhost:8080/tmp_easyui/assets/image/config/favicon/1664509842.png', 'http://localhost:8080/tmp_easyui/assets/image/config/login/1664510168.jpg', 'default', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `logs`
--

CREATE TABLE `logs` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `action` varchar(30) NOT NULL,
  `menu` varchar(30) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `logs`
--

INSERT INTO `logs` (`id`, `created_by`, `created_date`, `deleted`, `ip_address`, `action`, `menu`, `description`) VALUES
('0288534f7fe148a2afdc48c7297129', 'admin', '2022-09-29 17:16:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"fceea91875b14207b984db9706f05b94\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:47\"}'),
('0660ac9f81c845cdafa7ae93846354', 'admin', '2022-09-29 17:19:47', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:47\"}'),
('06c4e4ba9f274c0f871ae84ad71b9a', 'admin', '2022-09-29 17:04:22', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"44964312f0264429978158ada88843\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"962311b4039448699f25c8ef64f47053\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:04:22\"}'),
('07d8f42dd0d241d588b8f9612de34a', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"b679033b3256414b8f916c69f17674\",\"id\":\"746d6989a790471ca2e4de24a9f871bc\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('09d255d138c94a62907a4b04ab5258', 'admin', '2022-09-29 15:49:58', 0, '::1', 'Update', 'users', '{\"name\":\"Administrator\",\"position\":\"Senior Programer\",\"email\":\"hilman@pixelphy.id\",\"phone\":\"82217465425\",\"actived\":\"0\",\"avatar\":null,\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 15:49:58\"}'),
('09d953f2d60145d8a35b256abee0b8', 'admin', '2022-09-29 17:03:57', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"m_view\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"65bdf777a5564d5c94068feb0edcb96b\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:03:57\"}'),
('1509cec5954c44e29a4c4982dc478c', 'admin', '2022-09-30 08:36:07', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Position\",\"link\":\"admin\\/position\",\"sort\":\"6\",\"state\":\"\",\"id\":\"da6475bd2790413b9fa10c2f02c83d8c\",\"created_by\":\"admin\",\"created_date\":\"2022-09-30 08:36:07\"}'),
('1652a7baaa9745f49754996e006dd8', 'admin', '2022-09-29 17:16:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"b1115a32d7d34e8da31c971838b63a5e\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:47\"}'),
('16a1d25f057e4836a7d1a890daa979', 'admin', '2022-09-29 17:19:31', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:31\"}'),
('1cb5b543b7ce4ad6a5007bd8396bba', 'admin', '2022-09-29 17:19:53', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:53\"}'),
('1f5c39a4258740ceb2d7d45d560835', 'admin', '2022-09-29 17:19:52', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:52\"}'),
('2065247ccaf7459a8bd09fcb3fb726', 'admin', '2022-09-29 17:03:33', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"b679033b3256414b8f916c69f17674\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"04a7682cc50247a8a75f609d17e14ae4\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:03:33\"}'),
('2402a58456fc41258fc50cb6ce0909', 'admin', '2022-09-29 17:19:53', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:53\"}'),
('250218c1d99749ffa157f19fe6d0b3', 'admin', '2022-09-29 17:19:38', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:38\"}'),
('255967fef7064680b0d961d47955a2', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"e3c31e10b6c64e119b068ae4b73be6\",\"id\":\"af473d04fefb479e967d32fd497e2e4e\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('2699e29fd46641dabd7ffd84808443', 'admin', '2022-09-29 16:35:49', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Setting Users\",\"link\":\"admin\\/setting_users\",\"sort\":\"6\",\"state\":\"\",\"id\":\"c8f8362a5f6c432ab27d37213f15d4c9\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:35:49\"}'),
('27c85784c4a64efd9c6be92b9e3538', 'admin', '2022-09-29 17:16:48', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"7dff041488ed44919a8d5388e2a1c48c\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:48\"}'),
('2b9367c2d60c44399a88ce700c9997', 'admin', '2022-09-29 17:04:13', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"e3c31e10b6c64e119b068ae4b73be6\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"c150682d482f4c25b613172ba9b880e1\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:04:13\"}'),
('2c95ca82ad9d48adb6d1daecf19072', 'admin', '2022-09-29 16:36:23', 0, '::1', 'Update', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Setting Menus\",\"link\":\"admin\\/setting_menus\",\"sort\":\"5\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 16:36:23\"}'),
('2cf0dcda01844a61bb95410e91b3f8', 'admin', '2022-09-29 16:06:30', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120004\"}'),
('2d2f6b38953d45f9bef98deabf3b24', 'admin', '2022-09-29 17:19:46', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:46\"}'),
('2ea94be49fac437fa5f50cd8df2497', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"55b32608d41d4d948e9eccee162be15c\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('2eefb56665ea47ed96ffde92566da9', 'admin', '2022-09-29 16:06:30', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120007\"}'),
('30ca34f3d67a4cb79e75f0e3768e70', 'admin', '2022-09-30 04:36:04', 0, '::1', 'Create', 'users', '{\"number\":\"201602263\",\"name\":\"Sri Nuraeni\",\"username\":\"sri_nura\",\"password\":\"nuraeni\",\"position\":\"Staff Accounting\",\"email\":\"srinuraeni204@gmail.com\",\"phone\":\"82249564622\",\"actived\":\"0\",\"avatar\":null,\"id\":\"fc39bff493944935b50cd620e3214d1a\",\"created_by\":\"admin\",\"created_date\":\"2022-09-30 04:36:04\"}'),
('3219351f20e54c4087f90ba686ee3b', 'admin', '2022-09-29 16:36:15', 0, '::1', 'Update', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Setting Menu\",\"link\":\"admin\\/setting_menus\",\"sort\":\"5\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 16:36:15\"}'),
('33af1cd2f80b41efb118aa899a08fb', 'admin', '2022-09-29 16:07:18', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"MNU-2021120002\",\"name\":\"Menu\",\"link\":\"admin\\/menus\",\"sort\":\"2\",\"state\":\"\",\"id\":\"86b3f8823d634634965c4bffa382088f\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:07:18\"}'),
('354b2ab1792c4c99a64bba46af15f7', 'admin', '2022-09-29 17:19:39', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:39\"}'),
('354c7307fe0a4f06b9bcf410f16d88', 'admin', '2022-09-29 17:19:33', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:33\"}'),
('3787a9d17c0d4d4cbb0f9a778ad5f8', 'admin', '2022-09-29 15:49:33', 0, '::1', 'Update', 'users', '{\"name\":\"Administrator\",\"position\":\"Senior Programer\",\"email\":\"hilman@pixelphy.id\",\"phone\":\"82217465425\",\"actived\":\"0\",\"avatar\":null,\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 15:49:33\"}'),
('38d2946c89764fa6885f02b6c1ac9d', 'admin', '2022-09-29 17:16:48', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"a78e5845f5fe43aaa6fd03f33817d99a\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:48\"}'),
('3da94e3f36d24d71a08a391f467f38', 'admin', '2022-09-29 17:04:17', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"c8f8362a5f6c432ab27d37213f15d4\",\"m_view\":\"on\",\"id\":\"18944e423a144c35b0c76050a4d74d62\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:04:17\"}'),
('4021428a33ef4e39b41a93a30786db', 'admin', '2022-09-29 17:01:01', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"m_view\":\"on\",\"id\":\"0fddaa1405bf4a6081704dba2da56b14\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:01:01\"}'),
('40489b4cb3d242f9b9f15aaabe0652', 'admin', '2022-09-29 17:19:32', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:32\"}'),
('405ea8dc2a814d3fa8e8552932b72b', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"b6be3cae71a347918e1b376a41b12707\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('41158ec5184442f184594bf55e8e82', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"d13439e3f2324450a69b4e0e50159a\",\"id\":\"4d92b7555a8f4d9d9e7045fab22c4476\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('42a52a3d218c4324b522856ac63b9b', 'admin', '2022-09-29 17:19:51', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:51\"}'),
('45c40eb639564b4289b7e860c3875a', 'admin', '2022-09-29 17:19:29', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:29\"}'),
('46cbba2aca5144cf8a2c0347896616', NULL, '2022-09-30 04:46:20', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Senior Programer\"}'),
('4718d5f7f60a4de1a55ffe349def47', 'admin', '2022-09-30 08:36:14', 0, '::1', 'Delete', 'menus', '{\"id\":\"da6475bd2790413b9fa10c2f02c83d\"}'),
('48b5ccb448ed43d3b1acca1a4cdfe2', 'admin', '2022-09-29 17:19:52', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:52\"}'),
('4a00c44b6ac94e69b13c50536a995c', 'admin', '2022-09-29 16:06:30', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120006\"}'),
('4b00b7c694a245a980524361b9e5f9', 'admin', '2022-09-29 17:16:48', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"cdfd452cf3f7461d929e322e14a4c5cf\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:48\"}'),
('4c264070d0a749c29861063f654e26', 'admin', '2022-09-29 17:19:36', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:36\"}'),
('4e95620011de4988b06c6f89dbf9a2', 'admin', '2022-09-29 16:04:44', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120002\"}'),
('5142706e9c544ba4ab6a519782889b', 'admin', '2022-09-29 17:19:37', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:37\"}'),
('544232739c87434faab20345c4cc29', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"e3c31e10b6c64e119b068ae4b73be6\",\"id\":\"1a84346639db41b1958d5a264dafebf7\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('56a827d1f89649bea0ccc58db13927', 'admin', '2022-09-30 04:40:59', 0, '::1', 'Update', 'approvals', '{\"table_name\":\"users\",\"user_approval_1\":\"hilman\",\"user_approval_2\":\"admin\",\"user_approval_3\":\"\",\"user_approval_4\":\"\",\"user_approval_5\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-30 04:40:59\"}'),
('56f8b2671727443a86abc75541255b', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"c8246967d0504b3893182b252c64a2b0\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('57b9a937c29e44a5bda286dc356ca8', 'admin', '2022-09-29 17:19:35', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:35\"}'),
('59b3061d54e14a75afa801488c111e', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"d13439e3f2324450a69b4e0e50159a\",\"id\":\"9b9c36848ff4460eb505761f37efaf92\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('5e239cf19bde4cddaf4aadb09e595b', 'admin', '2022-09-29 16:22:01', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Approval\",\"link\":\"admin\\/approvals\",\"sort\":\"1\",\"state\":\"\",\"id\":\"b679033b3256414b8f916c69f17674c8\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:22:01\"}'),
('6bd54698cfed4458ac8de6d09cd356', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"id\":\"62aa172fcf7c443aba135013fbcc5433\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('6ebd748b018e4762899becb40108ff', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"81e39f5cd7544f28b02f2fc341d90411\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('7108fdada621497c821589bd484cd2', 'admin', '2022-09-29 17:19:41', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:41\"}'),
('71b84d528476466e8549fa3067a7ee', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"1275439765e34195a32ea3352ba7f89d\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('77d3d48cdf4c4a768fd3205359c201', NULL, '2022-09-30 05:56:17', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Senior Programer\"}'),
('7a460872a9b648b39bbb82f4fa54c1', 'admin', '2022-09-29 16:36:50', 0, '::1', 'Update', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Menu\",\"link\":\"admin\\/menus\",\"sort\":\"3\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 16:36:50\"}'),
('7aa4d2d4dcf7427d9209a4014b49dd', 'admin', '2022-09-29 17:19:40', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:40\"}'),
('7f9ae5a998764411b5bd19597dca2d', 'admin', '2022-09-29 17:19:37', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:37\"}'),
('803ceb41bde14e6cb121b229b1dca3', 'admin', '2022-09-29 17:19:48', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:48\"}'),
('844dbc1391a24b369e01023e4e5d9d', 'admin', '2022-09-29 17:16:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"db80a1fbb68b470caedae7d4d4ae9a16\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:47\"}'),
('878c2f6b432e445db5dfb41dbb8237', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"id\":\"56c39e081a4d4d4c8db20e988f14cc0f\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('88f20df944aa493d8f2ad9f8dd3166', 'admin', '2022-09-29 17:19:34', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:34\"}'),
('8c0029d82f45442ca297e3b2c2ca84', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"6ccd20c54d1d415189120ec5cc6c81\",\"id\":\"c93d58aadd4b46ce94ac9e8af4f42c2c\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('91285a0db9e94a31897212db660f8e', 'admin', '2022-09-29 17:19:32', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:32\"}'),
('922f4a5971184ec68d6d4687fc6ee9', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"c8f8362a5f6c432ab27d37213f15d4\",\"id\":\"67bf89a8259942d799ead773394497d1\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('983cbfcb677846f19c67beec79b602', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"f7a3170a120d4a1ebcda2f0c862fc8da\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('9913e23e12a44cafae7f697e00be18', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"id\":\"adaa86ffce41477da77aa3f4c1d2a1d9\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('9db28e516e6a4a0fb076e62057d81b', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"6ccd20c54d1d415189120ec5cc6c81\",\"id\":\"d23a7719b3a34d02808d2ce7a8ed50eb\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('a002fa8667d94488a7b6a6d4f35867', 'admin', '2022-09-29 16:12:08', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Users\",\"link\":\"admin\\/users\",\"sort\":\"2\",\"state\":\"\",\"id\":\"44964312f0264429978158ada88843ec\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:12:08\"}'),
('a374b9cb57624a7b966a11c1005360', 'admin', '2022-09-29 16:05:52', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"\",\"name\":\"Administrator\",\"link\":\"\",\"sort\":\"1\",\"state\":\"closed\",\"id\":\"cf98f97766f6405590b26daa586e0053\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:05:52\"}'),
('a57cc057ce864fbbb84ebc9e2ccf1d', 'admin', '2022-09-29 16:36:31', 0, '::1', 'Update', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Setting Menu\",\"link\":\"admin\\/setting_menus\",\"sort\":\"5\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 16:36:31\"}'),
('ae074891a4ea4bdba5f7d4d37b4505', 'admin', '2022-09-29 16:32:23', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Logs\",\"link\":\"admin\\/logs\",\"sort\":\"4\",\"state\":\"\",\"id\":\"de3f6855009e49deb7fd2fdd0f3b3d0d\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:32:23\"}'),
('aea50c03603e46ac9fae0ea5addc30', 'admin', '2022-09-29 17:19:39', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:39\"}'),
('af274a9a1cad425e8531e8e1a6681b', NULL, '2022-09-30 04:42:06', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025843ed953fe6094ff765\",\"name\":\"Hilman Fadillah\",\"username\":\"hilman\",\"position\":\"Staff IT\"}'),
('b67aa9cdac094612979de40131ba64', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"44964312f0264429978158ada88843\",\"id\":\"060d3236e09646a3a28b7105a84bfddc\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('bd7b3c92af584936a7a4804e840bd9', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"b679033b3256414b8f916c69f17674\",\"id\":\"62317b82b87545c5922bdf32580e66f4\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('be6a9b0c68af4d76a7ff4760629462', NULL, '2022-09-30 04:48:11', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025843ed953fe6094ff765\",\"name\":\"Hilman Fadillah\",\"username\":\"hilman\",\"position\":\"Staff IT\"}'),
('be9a2093a0f74063b9b22e3e87ab56', 'admin', '2022-09-29 17:19:31', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:31\"}'),
('beefd6308df34f1a9d3ca8948340ce', 'admin', '2022-09-29 17:04:02', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"d13439e3f2324450a69b4e0e50159a\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"972a76a5a04e416e92a9b4e225c19c2c\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:04:02\"}'),
('c182693665a64dadb60540c6bbac3d', 'admin', '2022-09-29 17:03:46', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"6ccd20c54d1d415189120ec5cc6c81\",\"m_view\":\"on\",\"id\":\"836ff9fa6650482fbf81e4f49bb25591\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:03:46\"}'),
('c2c6262757594d098dc60ace4b597e', 'admin', '2022-09-29 17:19:26', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:26\"}'),
('c51d4362d89147f2bac9d17b4a3a3b', 'admin', '2022-09-29 16:35:33', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Setting Menu\",\"link\":\"admin\\/setting_menu\",\"sort\":\"5\",\"state\":\"\",\"id\":\"e3c31e10b6c64e119b068ae4b73be6e5\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:35:33\"}'),
('c5e72a8f4bbb47f1930d1c147d7299', NULL, '2022-09-30 04:50:17', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Senior Programer\"}'),
('c6adaf27ca6944e6943cfa9615fcf1', 'admin', '2022-09-29 16:06:30', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120008\"}'),
('ca31954f77f5437084b7cbe7a3f638', 'admin', '2022-09-29 17:19:51', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:51\"}'),
('caf228680d794b5bbc5a9e38a2fdbc', 'admin', '2022-09-29 16:07:08', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"MNU-2021120002\",\"name\":\"Users\",\"link\":\"admin\\/users\",\"sort\":\"1\",\"state\":\"\",\"id\":\"a723ac537ff94d31b6023714cfac983e\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:07:08\"}'),
('cc41f02d084845bd9bdb5ea9cc6114', 'admin', '2022-09-29 17:19:40', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:40\"}'),
('cd2edddd38eb4bc59fedf679e8fd5f', 'admin', '2022-09-29 17:16:48', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"a28240594bc24677aefc794570dd5ade\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:48\"}'),
('cd412c18df2e4d8eb89c5d7114a0ee', 'admin', '2022-09-30 04:47:29', 0, '::1', 'Create', 'users', '{\"number\":\"201602264\",\"name\":\"Dani Fahrizal Sucipto\",\"username\":\"danifs\",\"password\":\"123456\",\"position\":\"Staff Engineering\",\"email\":\"danifs@gmail.com\",\"phone\":\"8222466562\",\"actived\":\"0\",\"avatar\":null,\"id\":\"e52beaefb5b14b6a95f0936c1aabc5c4\",\"created_by\":\"admin\",\"created_date\":\"2022-09-30 04:47:29\"}'),
('ced31c9fd56a46d5a671766a6d83ac', 'admin', '2022-09-30 04:41:53', 0, '::1', 'Create', 'users', '{\"number\":\"201602264\",\"name\":\"Azi Taufiq Rahman\",\"username\":\"azitr\",\"password\":\"123456\",\"position\":\"Staff Production\",\"email\":\"azitaufiq@gmail.com\",\"phone\":\"81323256398\",\"actived\":\"0\",\"avatar\":null,\"id\":\"00813457e95a427086bf4870ee2e1ff7\",\"created_by\":\"admin\",\"created_date\":\"2022-09-30 04:41:53\"}'),
('d3c16cd0e18c44b1b7d72865a50b1a', NULL, '2022-09-30 05:55:55', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Senior Programer\"}'),
('d73802480ecc4c27a27067c8753772', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"53b92000ffe04848b0c75fcd1dae63f7\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('db99084ed3f0413bb8d39ef065a8fe', 'admin', '2022-09-29 17:19:45', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:45\"}'),
('deb0d69132a34e8996a9a792fa6169', 'admin', '2022-09-29 16:06:30', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120003\"}'),
('e117ef24096742ea86730feabc22fe', NULL, '2022-10-03 01:20:30', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Senior Programer\"}'),
('e13415d946f045f2b074720e3c6691', 'admin', '2022-09-29 17:19:30', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:30\"}'),
('e712638864d74fcf996c140638ca94', 'admin', '2022-09-29 17:16:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"26e670e96db7493c80d2181cb3e733e2\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:16:47\"}'),
('e8e617a091c0425e9e3b03750ff6e4', 'admin', '2022-09-30 04:40:46', 0, '::1', 'Create', 'approvals', '{\"table_name\":\"users\",\"user_approval_1\":\"admin\",\"user_approval_2\":\"\",\"user_approval_3\":\"\",\"user_approval_4\":\"\",\"user_approval_5\":\"\",\"id\":\"603bdaf4a2e944b38c6e679ae06f5361\",\"created_by\":\"admin\",\"created_date\":\"2022-09-30 04:40:46\"}'),
('e96c218ca75548efa32dfce36963cd', 'admin', '2022-09-29 16:06:30', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120005\"}'),
('eb9020b005be480aa26f4b238180f9', 'admin', '2022-09-29 17:19:35', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:35\"}'),
('ebd70a3eb5de44a8a7ef94a67c8bc4', 'admin', '2022-09-29 16:15:42', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Menu\",\"link\":\"admin\\/menus\",\"sort\":\"2\",\"state\":\"\",\"id\":\"d13439e3f2324450a69b4e0e50159a36\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:15:42\"}'),
('ebe35baa8a55451cbfa73e2088fdf5', 'admin', '2022-09-29 17:17:06', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":null,\"id\":\"6b6fb9c6f9654e80819c14b1986a661d\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:06\"}'),
('ec33eacd562e4af0bdd24eee649784', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"id\":\"432e6369776a430b8970eeca739e7103\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('ec798b59b5ac40fabd038eeb943498', 'admin', '2022-09-29 16:04:35', 0, '::1', 'Delete', 'menus', '{\"id\":\"MNU-2021120001\"}'),
('f59fff8ef87a4746bb2c11742143cf', 'admin', '2022-09-29 17:17:47', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"c8f8362a5f6c432ab27d37213f15d4\",\"id\":\"01f8d57e2d4c47c2a63dc3ce8515885c\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:17:47\"}'),
('f6418e47b26345339124aeec23978d', 'admin', '2022-09-29 16:41:40', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"name\":\"Config\",\"link\":\"admin\\/config\",\"sort\":\"7\",\"state\":\"\",\"id\":\"6ccd20c54d1d415189120ec5cc6c8143\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 16:41:40\"}'),
('f8ac120a7ab5496b8caf13894c595e', NULL, '2022-09-30 07:42:01', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Senior Programer\"}'),
('fa4e06fc4d284cc88bc95bf6940e2c', 'admin', '2022-09-29 17:18:39', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"44964312f0264429978158ada88843\",\"id\":\"2c192616c6374feebbbc2778dd4443bb\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 17:18:39\"}'),
('fa8e8b7551724edc8dab1f838c373c', NULL, '2022-09-30 05:51:18', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Senior Programer\"}'),
('fbf01c7a25e14b0aab749a5e94e95c', 'admin', '2022-09-29 15:52:33', 0, '::1', 'Create', 'users', '{\"number\":\"201602262\",\"name\":\"Hilman Fadillah\",\"username\":\"hilman\",\"password\":\"123456\",\"position\":\"Staff IT\",\"email\":\"hilmanf11@gmail.com\",\"phone\":\"8559299000\",\"actived\":\"0\",\"avatar\":null,\"id\":\"86f9f296025843ed953fe6094ff76525\",\"created_by\":\"admin\",\"created_date\":\"2022-09-29 15:52:33\"}'),
('fca4be1b10c74dfa81e55e920f3893', 'admin', '2022-09-29 17:19:30', 0, '::1', 'Update', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2022-09-29 17:19:30\"}');

-- --------------------------------------------------------

--
-- Struktur dari tabel `menus`
--

CREATE TABLE `menus` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `menus_id` varchar(30) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `name` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `link` text NOT NULL,
  `sort` int(11) NOT NULL,
  `icon` varchar(30) NOT NULL,
  `flag` varchar(10) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `menus`
--

INSERT INTO `menus` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `menus_id`, `number`, `name`, `description`, `link`, `sort`, `icon`, `flag`, `color`, `state`, `status`) VALUES
('44964312f0264429978158ada88843', 'admin', '2022-09-29 16:12:08', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Users', NULL, 'admin/users', 2, '', NULL, NULL, '', 0),
('6ccd20c54d1d415189120ec5cc6c81', 'admin', '2022-09-29 16:41:40', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Config', NULL, 'admin/config', 7, '', NULL, NULL, '', 0),
('b679033b3256414b8f916c69f17674', 'admin', '2022-09-29 16:22:01', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Approval', NULL, 'admin/approvals', 1, '', NULL, NULL, '', 0),
('c8f8362a5f6c432ab27d37213f15d4', 'admin', '2022-09-29 16:35:49', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Setting Users', NULL, 'admin/setting_users', 6, '', NULL, NULL, '', 0),
('cf98f97766f6405590b26daa586e00', 'admin', '2022-09-29 16:05:52', NULL, NULL, 0, '', NULL, 'Administrator', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('d13439e3f2324450a69b4e0e50159a', 'admin', '2022-09-29 16:15:42', 'admin', '2022-09-29 16:36:50', 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Menu', NULL, 'admin/menus', 3, '', NULL, NULL, '', 0),
('de3f6855009e49deb7fd2fdd0f3b3d', 'admin', '2022-09-29 16:32:23', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Logs', NULL, 'admin/logs', 4, '', NULL, NULL, '', 0),
('e3c31e10b6c64e119b068ae4b73be6', 'admin', '2022-09-29 16:35:33', 'admin', '2022-09-29 16:36:31', 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Setting Menu', NULL, 'admin/setting_menus', 5, '', NULL, NULL, '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `approvals_id` varchar(30) DEFAULT NULL,
  `users_id_from` varchar(30) DEFAULT NULL,
  `users_id_to` varchar(30) DEFAULT NULL,
  `table_id` varchar(30) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `notifications`
--

INSERT INTO `notifications` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `approvals_id`, `users_id_from`, `users_id_to`, `table_id`, `table_name`, `name`, `description`, `status`) VALUES
('8b0746da2a4544e7adb21c94e46de2', 'admin', '2022-09-30 04:47:29', NULL, NULL, 0, '603bdaf4a2e944b38c6e679ae06f53', 'admin', 'hilman', 'e52beaefb5b14b6a95f0936c1aabc5', 'users', 'CREATED APPROVAL', 'Sent a request on 30 September 2022 04:47:29  to approve data <b>USERS</b>', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_menus`
--

CREATE TABLE `setting_menus` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `menus_id` varchar(30) DEFAULT NULL,
  `m_view` varchar(5) DEFAULT NULL,
  `m_add` varchar(5) DEFAULT NULL,
  `m_edit` varchar(5) DEFAULT NULL,
  `m_delete` varchar(5) DEFAULT NULL,
  `m_upload` varchar(5) DEFAULT NULL,
  `m_download` varchar(5) DEFAULT NULL,
  `m_print` varchar(5) DEFAULT NULL,
  `m_excel` varchar(5) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `setting_menus`
--

INSERT INTO `setting_menus` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `menus_id`, `m_view`, `m_add`, `m_edit`, `m_delete`, `m_upload`, `m_download`, `m_print`, `m_excel`, `status`) VALUES
('04a7682cc50247a8a75f609d17e14a', 'admin', '2022-09-29 17:03:33', NULL, NULL, 0, 'b679033b3256414b8f916c69f17674', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('0fddaa1405bf4a6081704dba2da56b', 'admin', '2022-09-29 17:01:01', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('18944e423a144c35b0c76050a4d74d', 'admin', '2022-09-29 17:04:17', NULL, NULL, 0, 'c8f8362a5f6c432ab27d37213f15d4', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('65bdf777a5564d5c94068feb0edcb9', 'admin', '2022-09-29 17:03:57', NULL, NULL, 0, 'de3f6855009e49deb7fd2fdd0f3b3d', 'on', NULL, NULL, 'on', NULL, NULL, 'on', 'on', 0),
('836ff9fa6650482fbf81e4f49bb255', 'admin', '2022-09-29 17:03:46', NULL, NULL, 0, '6ccd20c54d1d415189120ec5cc6c81', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('962311b4039448699f25c8ef64f470', 'admin', '2022-09-29 17:04:22', NULL, NULL, 0, '44964312f0264429978158ada88843', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('972a76a5a04e416e92a9b4e225c19c', 'admin', '2022-09-29 17:04:02', NULL, NULL, 0, 'd13439e3f2324450a69b4e0e50159a', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('c150682d482f4c25b613172ba9b880', 'admin', '2022-09-29 17:04:13', NULL, NULL, 0, 'e3c31e10b6c64e119b068ae4b73be6', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_users`
--

CREATE TABLE `setting_users` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `users_id` varchar(30) DEFAULT NULL,
  `menus_id` varchar(30) DEFAULT NULL,
  `v_view` tinyint(1) NOT NULL DEFAULT 0,
  `v_add` tinyint(1) NOT NULL DEFAULT 0,
  `v_edit` tinyint(1) NOT NULL DEFAULT 0,
  `v_delete` tinyint(1) NOT NULL DEFAULT 0,
  `v_upload` tinyint(1) NOT NULL DEFAULT 0,
  `v_download` tinyint(1) NOT NULL DEFAULT 0,
  `v_print` tinyint(1) NOT NULL DEFAULT 0,
  `v_excel` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `setting_users`
--

INSERT INTO `setting_users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `users_id`, `menus_id`, `v_view`, `v_add`, `v_edit`, `v_delete`, `v_upload`, `v_download`, `v_print`, `v_excel`, `status`) VALUES
('2c192616c6374feebbbc2778dd4443', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:46', 0, 'admin', '44964312f0264429978158ada88843', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('56c39e081a4d4d4c8db20e988f14cc', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:52', 0, 'admin', 'de3f6855009e49deb7fd2fdd0f3b3d', 1, 0, 0, 1, 0, 0, 1, 1, 0),
('62aa172fcf7c443aba135013fbcc54', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:26', 0, 'admin', 'cf98f97766f6405590b26daa586e00', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('67bf89a8259942d799ead773394497', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:31', 0, 'admin', 'c8f8362a5f6c432ab27d37213f15d4', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('746d6989a790471ca2e4de24a9f871', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:48', 0, 'admin', 'b679033b3256414b8f916c69f17674', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('9b9c36848ff4460eb505761f37efaf', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:51', 0, 'admin', 'd13439e3f2324450a69b4e0e50159a', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('af473d04fefb479e967d32fd497e2e', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:53', 0, 'admin', 'e3c31e10b6c64e119b068ae4b73be6', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('c93d58aadd4b46ce94ac9e8af4f42c', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:30', 0, 'admin', '6ccd20c54d1d415189120ec5cc6c81', 1, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `actived` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `username`, `password`, `email`, `phone`, `position`, `avatar`, `actived`, `status`) VALUES
('86f9f296025243ed953fe6014ff765', 'admin', '2021-12-26 11:24:58', 'admin', '2022-09-29 15:49:58', 0, '1', 'Administrator', '', 'admin', 'admin@123#', 'hilman@pixelphy.id', '82217465425', 'Senior Programer', NULL, 0, 0),
('86f9f296025843ed953fe6094ff765', 'admin', '2022-09-29 15:52:33', NULL, NULL, 0, '201602262', 'Hilman Fadillah', NULL, 'hilman', '123456', 'hilmanf11@gmail.com', '8559299000', 'Staff IT', NULL, 0, 0),
('e52beaefb5b14b6a95f0936c1aabc5', 'admin', '2022-09-30 04:47:29', NULL, NULL, 0, '201602264', 'Dani Fahrizal Sucipto', NULL, 'danifs', '123456', 'danifs@gmail.com', '8222466562', 'Staff Engineering', NULL, 0, 0),
('fc39bff493944935b50cd620e3214d', 'admin', '2022-09-30 04:36:04', NULL, NULL, 0, '201602263', 'Sri Nuraeni', NULL, 'sri_nura', 'nuraeni', 'srinuraeni204@gmail.com', '82249564622', 'Staff Accounting', NULL, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `approvals_id` (`approvals_id`);

--
-- Indeks untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `menus_id` (`menus_id`);

--
-- Indeks untuk tabel `setting_users`
--
ALTER TABLE `setting_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `menus_id` (`menus_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `config`
--
ALTER TABLE `config`
  ADD CONSTRAINT `config_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `config_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`approvals_id`) REFERENCES `approvals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD CONSTRAINT `setting_menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_3` FOREIGN KEY (`menus_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `setting_users`
--
ALTER TABLE `setting_users`
  ADD CONSTRAINT `setting_users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_3` FOREIGN KEY (`menus_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
