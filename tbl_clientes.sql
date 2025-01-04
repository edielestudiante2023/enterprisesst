-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 06, 2024 at 07:39 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `enterprise_sst2024`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_clientes`
--

CREATE TABLE `tbl_clientes` (
  `id_cliente` int NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fecha_ingreso` date NOT NULL,
  `nit_cliente` int NOT NULL,
  `nombre_cliente` varchar(255) NOT NULL,
  `usuario` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `correo_cliente` varchar(255) NOT NULL,
  `telefono_1_cliente` varchar(255) NOT NULL,
  `telefono_2_cliente` varchar(255) DEFAULT NULL,
  `direccion_cliente` varchar(255) NOT NULL,
  `persona_contacto_compras` varchar(255) NOT NULL,
  `codigo_actividad_economica` varchar(255) NOT NULL,
  `nombre_rep_legal` varchar(255) NOT NULL,
  `cedula_rep_legal` varchar(255) NOT NULL,
  `fecha_fin_contrato` date DEFAULT NULL,
  `ciudad_cliente` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo','pendiente') NOT NULL,
  `id_consultor` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tbl_clientes`
--

INSERT INTO `tbl_clientes` (`id_cliente`, `datetime`, `fecha_ingreso`, `nit_cliente`, `nombre_cliente`, `usuario`, `password`, `correo_cliente`, `telefono_1_cliente`, `telefono_2_cliente`, `direccion_cliente`, `persona_contacto_compras`, `codigo_actividad_economica`, `nombre_rep_legal`, `cedula_rep_legal`, `fecha_fin_contrato`, `ciudad_cliente`, `estado`, `id_consultor`) VALUES
(1, '2024-08-06 02:34:30', '2024-08-05', 123, 'prueba', 'edison.cuervo@cycloidtalent.com', '123456', 'edison.cuervo@cycloidtalent.com', '123', '123', '123', 'a', '123', 'a', '123', '2024-08-31', 'Soacha', 'activo', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_clientes`
--
ALTER TABLE `tbl_clientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `id_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_clientes`
--
ALTER TABLE `tbl_clientes`
  MODIFY `id_cliente` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
