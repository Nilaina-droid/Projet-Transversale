-- --------------------------------------------------------
-- Hôte:                         127.0.0.1
-- Version du serveur:           8.0.45 - MySQL Community Server - GPL
-- SE du serveur:                Win64
-- HeidiSQL Version:             12.15.0.7171
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Listage des données de la table gestion_ru.etudiant : ~3 rows (environ)
INSERT INTO `etudiant` (`id_etudiant`, `matricule`, `nom`, `prenom`, `email`, `telephone`, `mot_de_passe`, `filiere`, `date_inscription`) VALUES
	(1, 'SE20250032', 'Randriamalala', 'Tahiry Nomenjanahry', 'tahiry07@gmail.com', '0372025400', '$2y$10$olILl6AaYuqOvGGpm8ktNeDwNZNln68N8du.oAzWkDxh3oe5GmotO', 'Informatique', '2026-06-23 20:45:14'),
	(2, 'SE20250227', 'RAMAROSON', 'Florion Ny Aly N\'ilaina', 'ramarosonnyaly@gmail.com', '0339675653', '$2y$10$TfGFcqT8M9.VMSWCBfngxuvm7qudBkralICAXivJ9O.lGv9TWAKlq', 'Informatique', '2026-06-24 06:01:21'),
	(3, 'SE20250033', 'RANDRIARIMANANA', 'Miantsa Antonny', 'tonnyrandria@gmail.com', '0387460524', '$2y$10$7M2GDiHf7xZKSIHEI2S1aurYKMPolr0XNhEPbmBw3XI3k9PzbrT9W', 'Informatique', '2026-06-24 06:04:12');

-- Listage des données de la table gestion_ru.gestionnaire : ~1 rows (environ)
INSERT INTO `gestionnaire` (`id_gestionnaire`, `identifiant`, `mot_de_passe`, `nom`, `prenom`, `date_creation`) VALUES
	(1, 'admin', '$2y$10$55bRHopfP8NEK/xbvI9PCOAPY5TwYzvFpXer7.kr4zHvp0.seBMJ2', 'Admin', 'Gestionnaire', '2026-06-23 19:58:20');

-- Listage des données de la table gestion_ru.menu : ~1 rows (environ)
INSERT INTO `menu` (`id_menu`, `semaine_du`, `semaine_au`, `date_publication`, `statut`, `id_gestionnaire`) VALUES
	(1, '2026-06-24', '2026-06-24', '2026-06-24 03:57:03', 'PUBLIE', 1);

-- Listage des données de la table gestion_ru.plat : ~1 rows (environ)
INSERT INTO `plat` (`id_plat`, `nom_plat`, `descriptions`, `type_plat`, `calories`, `allergens`, `photo`) VALUES
	(1, 'Poulet frit', 'poulet frit avec sauce', 'Viande', NULL, 'arachides', NULL);

-- Listage des données de la table gestion_ru.quota : ~0 rows (environ)

-- Listage des données de la table gestion_ru.reservation : ~1 rows (environ)
INSERT INTO `reservation` (`id_reservation`, `date_reservation`, `statut`, `commentaire`, `id_etudiant`) VALUES
	(1, '2026-06-24 00:00:00', 'CONFIRMEE', 'repas au gluten , allergie aux noix', 1);

-- Listage des données de la table gestion_ru.service : ~0 rows (environ)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
