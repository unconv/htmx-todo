CREATE TABLE `todos` (
  `id` int NOT NULL,
  `todo` varchar(256) NOT NULL,
  `category` varchar(64) NOT NULL,
  `done` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `todos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `todos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
