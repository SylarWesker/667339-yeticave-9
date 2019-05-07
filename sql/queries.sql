-- Добавление данных.
USE yeticave;

-- Добавление категорий.
INSERT INTO stuff_category (name, symbol_code) 
VALUES
('Доски и лыжи', 'boards'), 
('Крепления', 'attachment'),
('Ботинки', 'boots'), 
('Одежда', 'clothing'), 
('Инструменты', 'tools'), 
('Разное', 'other');

-- Добавление пользователей.
INSERT INTO user (name, password, email, contacts)
VALUES
('admin', 'hashed_password', 'hackerman@gmail.com', 'Top secret info'),
('simple_user', 'hashed_password', 'vasya2005@mail.ru', 'New-York city честно');

-- Добавление объявлений.
INSERT INTO lot (author_id, category_id, name, start_price, image_url, step_bet, end_date)
VALUES
(1, 1, '2014 Rossignol District Snowboard', 10999, 'img/lot-1.jpg', 500, NOW() + INTERVAL 7 day),
(1, 1, 'DC Ply Mens 2016/2017 Snowboard', 159999, 'img/lot-2.jpg', 1000, NOW() + INTERVAL 7 day),
(1, 2, 'Крепления Union Contact Pro 2015 года размер L/XL', 8000, 'img/lot-3.jpg', 100, NOW() + INTERVAL 7 day),
(2, 3, 'Ботинки для сноуборда DC Mutiny Charocal', 10999, 'img/lot-4.jpg', 500, NOW() + INTERVAL 7 day),
(2, 4, 'Куртка для сноуборда DC Mutiny Charocal', 7500, 'img/lot-5.jpg', 100, NOW() + INTERVAL 7 day),
(2, 6, 'Маска Oakley Canopy', 5400, 'img/lot-6.jpg', 100, NOW() + INTERVAL 7 day);

-- Добавление ставок.
INSERT INTO bet (lot_id, user_id, price)
VALUES 
(1, 1, 15000),
(2, 2, 165000);


-- Запросы (потом вынести в отдельный файл).

-- Получить все категории.
SELECT * FROM stuff_category; 
-- если только названия
SELECT name FROM stuff_category;
-- Узнаем есть ли категория с указаным названием.
SELECT COUNT(*) as count_categories FROM stuff_category WHERE name = 'Разное'


-- Получить самые новые, открытые лоты. 
-- Каждый лот должен включать название, стартовую цену, ссылку на изображение, цену, название категории;
-- SELECT  l.name,
--         l.start_price, 
--         l.image_url, 
--         l.creation_date,
--         l.end_date,
--         cat.name as 'category', 
--         l.description,
--         max(b.price) as 'max_price'
-- FROM lot as l
-- JOIN stuff_category as cat on l.category_id = cat.id
-- LEFT JOIN bet as b on l.id = b.lot_id
-- WHERE l.end_date IS NOT NULL AND l.end_date > NOW()
-- GROUP BY l.name,
--         l.start_price, 
--         l.image_url, 
--         l.creation_date,
--         l.end_date,
--         cat.name, 
--         l.description
-- ORDER BY l.creation_date DESC

SELECT  l.*,
        cat.name category, 
        IFNULL(max(b.price), l.start_price) current_price
FROM lot as l
LEFT JOIN stuff_category as cat on l.category_id = cat.id
LEFT JOIN bet as b on l.id = b.lot_id
WHERE l.end_date IS NOT NULL 
      AND l.end_date > NOW() 
      AND l.winner_id IS NULL
GROUP BY l.id,
         cat.name
ORDER BY l.creation_date DESC

-- Показать лот по его ID
SELECT  l.name, 
        l.start_price,
        l.creation_date, 
        l.end_date, 
        l.step_bet, 
        us.name as 'author', 
        cat.name as 'category' 
FROM lot as l
LEFT JOIN stuff_category as cat on l.category_id = cat.id
LEFT JOIN user as us on l.author_id = us.id
WHERE l.id = 1; -- тут указать id лота


-- Обновить название лота по его id
UPDATE lot SET name = 'new lot name'
WHERE id = 1; -- тут указать id нужного лота


-- Получить список самых свежих ставок для лота по его идентификатору.
SELECT  b.create_date, 
        b.price, 
        us.name as 'bet creator', 
        l.name as 'lot name' 
FROM bet as b
LEFT JOIN user as us on b.user_id = us.id
LEFT JOIN lot as l on b.lot_id = l.id
WHERE b.lot_id = 1 -- тут указать id лота
ORDER BY b.create_date DESC 
LIMIT 3;

-- Пример запроса добавления лота.
INSERT INTO `lot`(  `name`, 
                    `description`, 
                    `image_url`, 
                    `start_price`, 
                    `end_date`, 
                    `step_bet`, 
                    `author_id`, 
                    `category_id`) 
VALUES ('new lot example', 
        'lot desc',
        NULL,
        1000,
        NOW() + INTERVAL 30 DAY,
        50,
        1,
        1)