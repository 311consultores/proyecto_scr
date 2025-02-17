-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: db_scr
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bitacora` (
  `id_bitacora` int NOT NULL AUTO_INCREMENT,
  `proyecto` varchar(300) DEFAULT NULL,
  `bSitio` int DEFAULT NULL,
  `sitio_id` int DEFAULT NULL,
  `cliente_id` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `equipo` varchar(300) DEFAULT NULL,
  `folio_reporte` varchar(15) DEFAULT NULL,
  `bFinalizado` int DEFAULT '0',
  `dt_creacion` date DEFAULT NULL,
  `activo` int DEFAULT NULL,
  PRIMARY KEY (`id_bitacora`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (6,'Proyecto Alabanha',NULL,NULL,0,'2025-02-03','Demoledores','Mai-9371',0,'2025-02-03',1),(7,'Proyecto',NULL,NULL,0,'2025-02-15','Jgfdaawdff','Reporte',0,'2025-02-04',1),(8,'',1,2,2,'2025-02-12','maniobras','ICA-02-01',0,'2025-02-12',1),(9,'ESTO ES UNA PRUEBA',0,0,2,'2025-02-13','EQUPO DE MONITOREO','ICA-02-02',0,'2025-02-13',1);
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_clientes`
--

DROP TABLE IF EXISTS `cat_clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_clientes` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `cliente` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abreviatura` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` int DEFAULT NULL,
  PRIMARY KEY (`id_cliente`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_clientes`
--

LOCK TABLES `cat_clientes` WRITE;
/*!40000 ALTER TABLE `cat_clientes` DISABLE KEYS */;
INSERT INTO `cat_clientes` VALUES (1,'EIT','EIT',1),(2,'ICAVE','ICA',1),(3,'TIMSA','TIM',1),(4,'LCTP','LCT',1),(5,'CMSA','CMS',1),(6,'GM','GM',1),(7,'APMT','APM',1),(8,'OTRO','OTR',1);
/*!40000 ALTER TABLE `cat_clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cat_sitios`
--

DROP TABLE IF EXISTS `cat_sitios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cat_sitios` (
  `id_sitio` int NOT NULL AUTO_INCREMENT,
  `sitio` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `abreviatura` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` int DEFAULT NULL,
  PRIMARY KEY (`id_sitio`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cat_sitios`
--

LOCK TABLES `cat_sitios` WRITE;
/*!40000 ALTER TABLE `cat_sitios` DISABLE KEYS */;
INSERT INTO `cat_sitios` VALUES (1,'ENSENADA','ENS',1),(2,'GUAYMAS','GYS',1),(3,'LAZARO CARDENAS','LZC',1),(4,'MANZANILLO','MZA',1),(5,'VERACRUZ','VRZ',1),(6,'PROGRESO','PRG',1),(7,'EXTRANJERO','EXT',1);
/*!40000 ALTER TABLE `cat_sitios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `det_bitacora`
--

DROP TABLE IF EXISTS `det_bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `det_bitacora` (
  `id_actividad` int NOT NULL AUTO_INCREMENT,
  `bitacora_id` int DEFAULT NULL,
  `orden_trabajo` varchar(300) DEFAULT NULL,
  `equipo` varchar(300) DEFAULT NULL,
  `observacion` text,
  `hora_creacion` time DEFAULT NULL,
  `activo` int DEFAULT NULL,
  PRIMARY KEY (`id_actividad`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `det_bitacora`
--

LOCK TABLES `det_bitacora` WRITE;
/*!40000 ALTER TABLE `det_bitacora` DISABLE KEYS */;
INSERT INTO `det_bitacora` VALUES (12,9,'OT-0948','TROMPETA','ESTO ES UNA OBSERVACION SIMPLE','15:47:58',1);
/*!40000 ALTER TABLE `det_bitacora` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-02-14 17:39:25
