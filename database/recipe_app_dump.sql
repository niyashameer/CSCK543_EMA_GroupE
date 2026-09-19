CREATE DATABASE  IF NOT EXISTS `recipe_app` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `recipe_app`;
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: recipe_app
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `allergens`
--

DROP TABLE IF EXISTS `allergens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `allergens` (
  `allergen_id` int NOT NULL AUTO_INCREMENT,
  `allergen_name` varchar(100) NOT NULL,
  PRIMARY KEY (`allergen_id`),
  UNIQUE KEY `allergen_name` (`allergen_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `allergens`
--

LOCK TABLES `allergens` WRITE;
/*!40000 ALTER TABLE `allergens` DISABLE KEYS */;
INSERT INTO `allergens` VALUES (5,'Celery'),(1,'Gluten'),(2,'Milk'),(3,'Soy'),(4,'Tree nuts');
/*!40000 ALTER TABLE `allergens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL,
  `category_type` enum('Course','Dietary','Cuisine','Other') NOT NULL DEFAULT 'Other',
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `category_name` (`category_name`,`category_type`),
  KEY `idx_categories_type` (`category_type`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (2,'Breakfast','Course'),(5,'Dairy-free','Dietary'),(6,'Egg-free','Dietary'),(8,'Gluten-free','Dietary'),(9,'Healthy','Dietary'),(12,'Indian','Cuisine'),(11,'Italian','Cuisine'),(1,'Main','Course'),(14,'Meat','Other'),(13,'Middle Eastern','Cuisine'),(7,'Nut-free','Dietary'),(10,'Pregnancy-friendly','Dietary'),(3,'Vegan','Dietary'),(4,'Vegetarian','Dietary');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favourites`
--

DROP TABLE IF EXISTS `favourites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favourites` (
  `user_id` int NOT NULL,
  `recipe_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`recipe_id`),
  KEY `recipe_id` (`recipe_id`),
  CONSTRAINT `favourites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `favourites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favourites`
--

LOCK TABLES `favourites` WRITE;
/*!40000 ALTER TABLE `favourites` DISABLE KEYS */;
/*!40000 ALTER TABLE `favourites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredient_allergens`
--

DROP TABLE IF EXISTS `ingredient_allergens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredient_allergens` (
  `ingredient_id` int NOT NULL,
  `allergen_id` int NOT NULL,
  PRIMARY KEY (`ingredient_id`,`allergen_id`),
  KEY `allergen_id` (`allergen_id`),
  CONSTRAINT `ingredient_allergens_ibfk_1` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE,
  CONSTRAINT `ingredient_allergens_ibfk_2` FOREIGN KEY (`allergen_id`) REFERENCES `allergens` (`allergen_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredient_allergens`
--

LOCK TABLES `ingredient_allergens` WRITE;
/*!40000 ALTER TABLE `ingredient_allergens` DISABLE KEYS */;
INSERT INTO `ingredient_allergens` VALUES (19,1),(21,1),(26,1),(59,1),(9,2),(20,2),(31,2),(43,2),(44,2),(23,3),(57,5);
/*!40000 ALTER TABLE `ingredient_allergens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ingredients` (
  `ingredient_id` int NOT NULL AUTO_INCREMENT,
  `ingredient_name` varchar(150) NOT NULL,
  PRIMARY KEY (`ingredient_id`),
  UNIQUE KEY `ingredient_name` (`ingredient_name`),
  KEY `idx_ingredients_name` (`ingredient_name`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredients`
--

LOCK TABLES `ingredients` WRITE;
/*!40000 ALTER TABLE `ingredients` DISABLE KEYS */;
INSERT INTO `ingredients` VALUES (22,'Baking powder'),(17,'Balsamic vinegar'),(46,'Basmati rice'),(15,'Bay leaves'),(5,'Black pepper'),(6,'Caster sugar'),(57,'Celery salt'),(31,'Cheese'),(13,'Chopped tomatoes'),(28,'Courgette'),(43,'Double cream'),(30,'Dried chilli flakes'),(52,'Dried mint'),(7,'Dried oregano'),(51,'Flatleaf parsley'),(8,'Fresh basil'),(39,'Fresh coriander'),(40,'Fresh mint'),(3,'Garlic'),(58,'Garlic granules'),(54,'Garlic oil'),(34,'Ginger'),(41,'Green chillies'),(37,'Ground cardamom'),(56,'Ground coriander'),(36,'Ground cumin'),(35,'Kashmiri chilli powder'),(42,'Lamb'),(11,'Lean minced beef'),(49,'Lemon juice'),(38,'Lime'),(14,'Marinated mushrooms'),(44,'Milk'),(1,'Olive oil'),(2,'Onion'),(53,'Oyster mushrooms'),(55,'Paprika'),(20,'Parmesan'),(32,'Passata'),(27,'Pepper'),(62,'Pickled chillies'),(59,'Pitta bread'),(47,'Pomegranate seeds'),(29,'Red onion'),(12,'Red wine'),(48,'Rose harissa'),(45,'Saffron'),(4,'Sea salt'),(21,'Self-raising flour'),(26,'Self-raising wholemeal flour'),(10,'Smoked streaky bacon'),(23,'Soya milk'),(19,'Spaghetti'),(18,'Sun-dried tomatoes'),(25,'Sunflower oil'),(16,'Thyme'),(61,'Tomatoes'),(24,'Vanilla extract'),(33,'Vegetable oil'),(60,'White cabbage'),(50,'White wine vinegar'),(9,'Yoghurt');
/*!40000 ALTER TABLE `ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ratings`
--

DROP TABLE IF EXISTS `ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratings` (
  `rating_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `recipe_id` int NOT NULL,
  `taste_rating` tinyint NOT NULL,
  `difficulty_rating` tinyint NOT NULL,
  `presentation_rating` tinyint NOT NULL,
  `review` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rating_id`),
  UNIQUE KEY `user_id` (`user_id`,`recipe_id`),
  KEY `idx_ratings_recipe` (`recipe_id`),
  CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  CONSTRAINT `ratings_chk_1` CHECK ((`taste_rating` between 1 and 5)),
  CONSTRAINT `ratings_chk_2` CHECK ((`difficulty_rating` between 1 and 5)),
  CONSTRAINT `ratings_chk_3` CHECK ((`presentation_rating` between 1 and 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ratings`
--

LOCK TABLES `ratings` WRITE;
/*!40000 ALTER TABLE `ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_categories`
--

DROP TABLE IF EXISTS `recipe_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_categories` (
  `recipe_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`recipe_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `recipe_categories_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_categories`
--

LOCK TABLES `recipe_categories` WRITE;
/*!40000 ALTER TABLE `recipe_categories` DISABLE KEYS */;
INSERT INTO `recipe_categories` VALUES (1,1),(3,1),(4,1),(5,1),(2,2),(2,3),(2,4),(3,4),(5,4),(2,5),(1,6),(2,6),(3,6),(4,6),(5,6),(1,7),(3,7),(5,7),(4,8),(3,9),(5,9),(2,10),(3,10),(4,10),(5,10),(1,11),(3,11),(4,12),(5,13),(1,14),(4,14);
/*!40000 ALTER TABLE `recipe_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_ingredients`
--

DROP TABLE IF EXISTS `recipe_ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_ingredients` (
  `recipe_ingredient_id` int NOT NULL AUTO_INCREMENT,
  `recipe_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity` varchar(50) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `section_name` varchar(100) DEFAULT NULL,
  `display_order` int NOT NULL,
  PRIMARY KEY (`recipe_ingredient_id`),
  UNIQUE KEY `recipe_id` (`recipe_id`,`display_order`),
  KEY `ingredient_id` (`ingredient_id`),
  CONSTRAINT `recipe_ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE,
  CONSTRAINT `recipe_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_ingredients`
--

LOCK TABLES `recipe_ingredients` WRITE;
/*!40000 ALTER TABLE `recipe_ingredients` DISABLE KEYS */;
INSERT INTO `recipe_ingredients` VALUES (1,1,1,'2','tbsp','or sun-dried tomato oil',NULL,1),(2,1,10,'6','rashers','chopped',NULL,2),(3,1,2,'2','large','chopped',NULL,3),(4,1,3,'3','cloves','crushed',NULL,4),(5,1,11,'1','kg',NULL,NULL,5),(6,1,12,'2','large glasses',NULL,NULL,6),(7,1,13,'2 x 400','g cans',NULL,NULL,7),(8,1,14,'290','g jar','drained',NULL,8),(9,1,15,'2',NULL,'fresh or dried',NULL,9),(10,1,7,'1','tsp',NULL,NULL,10),(11,1,16,'1','tsp','dried or fresh',NULL,11),(12,1,17,'1','drizzle',NULL,NULL,12),(13,1,18,'12-14','halves','in oil',NULL,13),(14,1,4,NULL,NULL,'to taste',NULL,14),(15,1,5,NULL,NULL,'freshly ground',NULL,15),(16,1,8,'1','handful','torn',NULL,16),(17,1,19,'800g-1kg',NULL,'dried',NULL,17),(18,1,20,NULL,NULL,'freshly grated, to serve',NULL,18),(32,2,21,'125','g',NULL,NULL,1),(33,2,6,'2','tbsp',NULL,NULL,2),(34,2,22,'1','tsp',NULL,NULL,3),(35,2,4,'1','pinch',NULL,NULL,4),(36,2,23,'150','ml',NULL,NULL,5),(37,2,24,'1/4','tsp',NULL,NULL,6),(38,2,25,'4','tsp','for frying',NULL,7),(39,3,26,'125','g','plus extra for dusting','For the base',1),(40,3,4,'1','pinch',NULL,'For the base',2),(41,3,9,'125','g','full-fat plain','For the base',3),(42,3,27,'1',NULL,'yellow or orange, thinly sliced','For the topping',4),(43,3,28,'1',NULL,'cut into slices','For the topping',5),(44,3,29,'1',NULL,'cut into thin wedges','For the topping',6),(45,3,1,'1','tbsp','plus extra for drizzling','For the topping',7),(46,3,30,'1/2','tsp',NULL,'For the topping',8),(47,3,31,'50','g','mozzarella, cheddar or goats\' cheese','For the topping',9),(48,3,5,NULL,NULL,'freshly ground','For the topping',10),(49,3,8,NULL,NULL,'optional, to serve','For the topping',11),(50,3,32,'6','tbsp','approximately 100g','For the tomato sauce',12),(51,3,7,'1','tsp',NULL,'For the tomato sauce',13),(54,4,33,'5','tbsp',NULL,NULL,1),(55,4,2,'2',NULL,'finely sliced',NULL,2),(56,4,9,'200','g','Greek or natural',NULL,3),(57,4,34,'4','tbsp','finely grated',NULL,4),(58,4,3,'3','tbsp','finely grated',NULL,5),(59,4,35,'1-2','tsp',NULL,NULL,6),(60,4,36,'5','tsp',NULL,NULL,7),(61,4,37,'1','tsp',NULL,NULL,8),(62,4,4,'4','tsp',NULL,NULL,9),(63,4,38,'1',NULL,'juice only',NULL,10),(64,4,39,'30','g','finely chopped',NULL,11),(65,4,40,'30','g','finely chopped',NULL,12),(66,4,41,'3-4',NULL,'finely chopped',NULL,13),(67,4,42,'800','g','boneless, cut into bite-sized pieces',NULL,14),(68,4,43,'4','tbsp',NULL,NULL,15),(69,4,44,'1 1/2','tbsp','full-fat',NULL,16),(70,4,45,'1','tsp','strands',NULL,17),(71,4,46,'400','g',NULL,NULL,18),(72,4,47,'2','tbsp','optional garnish',NULL,19),(85,5,13,'400','g tin',NULL,'For the chilli sauce',1),(86,5,48,'2','tbsp',NULL,'For the chilli sauce',2),(87,5,6,'2','tsp',NULL,'For the chilli sauce',3),(88,5,49,'1','squeeze',NULL,'For the chilli sauce',4),(89,5,2,'1',NULL,'very thinly sliced','For the onion',5),(90,5,50,'2','tsp',NULL,'For the onion',6),(91,5,51,'20','g','finely chopped','For the onion',7),(92,5,9,'150','g','plain','For the yoghurt sauce',8),(93,5,52,'1','heaped tsp',NULL,'For the yoghurt sauce',9),(94,5,4,NULL,NULL,'to taste','For the yoghurt sauce',10),(95,5,5,NULL,NULL,'freshly ground','For the yoghurt sauce',11),(96,5,53,'500','g','thinly sliced','For the doner',12),(97,5,54,'2','tsp',NULL,'For the doner',13),(98,5,55,'2','tsp',NULL,'For the doner',14),(99,5,56,'2','heaped tsp',NULL,'For the doner',15),(100,5,57,'2','tsp',NULL,'For the doner',16),(101,5,58,'3','tsp',NULL,'For the doner',17),(102,5,59,'4',NULL,'white','For the doner',18),(103,5,60,'1/4','small','finely shredded','For the garnish',19),(104,5,61,'2',NULL,'sliced','For the garnish',20),(105,5,62,'4-6',NULL,'optional','For the garnish',21);
/*!40000 ALTER TABLE `recipe_ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipe_steps`
--

DROP TABLE IF EXISTS `recipe_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipe_steps` (
  `step_id` int NOT NULL AUTO_INCREMENT,
  `recipe_id` int NOT NULL,
  `step_number` int NOT NULL,
  `instruction` text NOT NULL,
  `duration_minutes` int NOT NULL,
  PRIMARY KEY (`step_id`),
  UNIQUE KEY `recipe_id` (`recipe_id`,`step_number`),
  CONSTRAINT `recipe_steps_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipe_steps`
--

LOCK TABLES `recipe_steps` WRITE;
/*!40000 ALTER TABLE `recipe_steps` DISABLE KEYS */;
INSERT INTO `recipe_steps` VALUES (1,1,1,'Cook the bacon, onions and garlic, then brown the minced beef. Add the wine and reduce before adding the tomatoes, mushrooms, herbs and balsamic vinegar.',20),(2,1,2,'Prepare the sun-dried tomatoes, add them to the sauce, season and simmer gently until the sauce becomes rich and thick. Finish with basil.',90),(3,1,3,'Allow the sauce to settle while cooking the spaghetti. Drain the pasta and serve with the sauce, parmesan and black pepper.',15),(4,2,1,'Mix the flour, sugar, baking powder and salt. Add the plant-based milk and vanilla and whisk until smooth.',5),(5,2,2,'Heat a non-stick frying pan, add oil and coat the surface.',3),(6,2,3,'Add portions of batter to the pan and spread each pancake to approximately 10cm in diameter.',3),(7,2,4,'Cook until bubbles appear, flip and cook the other side until lightly golden.',2),(8,2,5,'Keep cooked pancakes warm while repeating with the remaining batter, then serve with preferred toppings.',10),(9,3,1,'Preheat the oven.',5),(10,3,2,'Combine the pepper, courgette, red onion and oil, season and roast the vegetables.',15),(11,3,3,'Combine the flour, salt, yoghurt and water to form the pizza dough, then knead briefly.',5),(12,3,4,'Roll the dough into a thin oval shape suitable for the baking tray.',3),(13,3,5,'Remove the roasted vegetables and bake the pizza base before turning it over.',5),(14,3,6,'Mix the passata and oregano, spread onto the base, add vegetables, chilli and cheese, then bake until cooked.',10),(15,3,7,'Season with black pepper, drizzle with olive oil and add basil if desired.',2),(16,4,1,'Fry the sliced onions until lightly browned and crisp.',18),(17,4,2,'Combine half of the onions with yoghurt, ginger, garlic, spices, lime, herbs and chillies.',5),(18,4,3,'Coat the lamb in the marinade, cover and refrigerate.',480),(19,4,4,'Preheat the oven.',5),(20,4,5,'Warm the cream and milk with the saffron and leave to infuse.',30),(21,4,6,'Cook the basmati rice until just cooked but still firm, then drain.',8),(22,4,7,'Layer the lamb, rice, reserved onions, herbs and saffron mixture in a casserole.',10),(23,4,8,'Cover and bake, then allow the biryani to rest before serving. Garnish with pomegranate if desired.',80),(24,5,1,'Preheat the oven.',5),(25,5,2,'Heat the chopped tomatoes, harissa, sugar and lemon juice and reduce to form the chilli sauce.',10),(26,5,3,'Mix the sliced onion with white wine vinegar and parsley and set aside.',3),(27,5,4,'Combine the yoghurt and dried mint and season with salt and pepper.',2),(28,5,5,'Warm the pitta breads in the oven.',5),(29,5,6,'Dry-fry the mushrooms, add the seasonings and garlic oil, then add a little water and stir-fry briefly.',5),(30,5,7,'Split the pittas and fill with cabbage, tomato, onion and mushrooms. Finish with the chilli and yoghurt sauces.',5);
/*!40000 ALTER TABLE `recipe_steps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipes`
--

DROP TABLE IF EXISTS `recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recipes` (
  `recipe_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) NOT NULL,
  `description` text,
  `prep_time_minutes` int NOT NULL,
  `cook_time_minutes` int NOT NULL,
  `servings` int DEFAULT NULL,
  `difficulty` enum('Easy','Medium','Hard') NOT NULL DEFAULT 'Easy',
  `image_path` varchar(255) DEFAULT NULL,
  `source_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`recipe_id`),
  KEY `idx_recipes_title` (`title`),
  KEY `idx_recipes_difficulty` (`difficulty`),
  KEY `idx_recipes_prep_time` (`prep_time_minutes`),
  KEY `idx_recipes_cook_time` (`cook_time_minutes`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipes`
--

LOCK TABLES `recipes` WRITE;
/*!40000 ALTER TABLE `recipes` DISABLE KEYS */;
INSERT INTO `recipes` VALUES (1,'Spaghetti bolognese with mushrooms and sun-dried tomatoes','A rich spaghetti bolognese containing mushrooms, sun-dried tomatoes, herbs and beef.',30,120,8,'Medium','assets/images/recipes/spaghetti-bolognese.jpg','https://www.bbc.co.uk/food/recipes/spaghettibolognese_67868','2026-09-19 16:52:31'),(2,'Vegan pancakes','Fluffy vegan pancakes suitable for breakfast and served with optional toppings.',30,30,2,'Easy','assets/images/recipes/vegan-pancakes.jpg','https://www.bbc.co.uk/food/recipes/vegan_american_pancakes_76094','2026-09-19 16:52:31'),(3,'Healthy pizza','A quick vegetarian pizza with a yoghurt-based dough, roasted vegetables and cheese.',30,30,2,'Easy','assets/images/recipes/healthy-pizza.jpg','https://www.bbc.co.uk/food/recipes/healthy_pizza_55143','2026-09-19 16:52:31'),(4,'Easy lamb biryani','A layered lamb and basmati rice dish with herbs, spices and saffron.',480,120,8,'Medium','assets/images/recipes/lamb-biryani.jpg','https://www.bbc.co.uk/food/recipes/easy_lamb_biryani_46729','2026-09-19 16:52:31'),(5,'Mushroom doner','A vegetarian mushroom doner served in pitta bread with chilli sauce, yoghurt sauce and vegetables.',30,30,4,'Easy','assets/images/recipes/mushroom-doner.jpg','https://www.bbc.co.uk/food/recipes/mushroom_doner_22676','2026-09-19 16:52:31');
/*!40000 ALTER TABLE `recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_allergens`
--

DROP TABLE IF EXISTS `user_allergens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_allergens` (
  `user_id` int NOT NULL,
  `allergen_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`allergen_id`),
  KEY `allergen_id` (`allergen_id`),
  CONSTRAINT `user_allergens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_allergens_ibfk_2` FOREIGN KEY (`allergen_id`) REFERENCES `allergens` (`allergen_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_allergens`
--

LOCK TABLES `user_allergens` WRITE;
/*!40000 ALTER TABLE `user_allergens` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_allergens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_dietary_preferences`
--

DROP TABLE IF EXISTS `user_dietary_preferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_dietary_preferences` (
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`user_id`,`category_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `user_dietary_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `user_dietary_preferences_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_dietary_preferences`
--

LOCK TABLES `user_dietary_preferences` WRITE;
/*!40000 ALTER TABLE `user_dietary_preferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_dietary_preferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-19 17:52:43
