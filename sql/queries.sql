-- Добавление данных.
USE yeticave;

-- Добавление категорий.
INSERT INTO stuff_categories (name, symbol_code) VALUES
('Доски и лыжи', 'boards'), ('Крепления', 'attachment'),
('Ботинки', 'boots'), ('Одежда', 'clothing'), 
('Инструменты', 'tools'), ('Разное', 'other');

-- Добавление пользователей.
INSERT INTO users (name, password, email, contacts)
VALUES
('admin', 'hashed_password', 'hackerman@gmail.com', 'Top secret info'),
('simple_user', 'hashed_password', 'vasya2005@mail.ru', 'New-York city честно');

-- Добавление объявлений.
INSERT INTO lots (id_author, id_category, name, start_price, image_url, step_bet, date_end)
VALUES
(1, 1, '2014 Rossignol District Snowboard', 10999, 'img/lot-1.jpg', 500, NOW() + INTERVAL 7 day),
(1, 1, 'DC Ply Mens 2016/2017 Snowboard', 159999, 'img/lot-2.jpg', 1000, NOW() + INTERVAL 7 day),
(1, 2, 'Крепления Union Contact Pro 2015 года размер L/XL', 8000, 'img/lot-3.jpg', 100, NOW() + INTERVAL 7 day),
(2, 3, 'Ботинки для сноуборда DC Mutiny Charocal', 10999, 'img/lot-4.jpg', 500, NOW() + INTERVAL 7 day),
(2, 4, 'Куртка для сноуборда DC Mutiny Charocal', 7500, 'img/lot-5.jpg', 100, NOW() + INTERVAL 7 day),
(2, 6, 'Маска Oakley Canopy', 5400, 'img/lot-6.jpg', 100, NOW() + INTERVAL 7 day);

-- Добавление ставок.
INSERT INTO bets (lot_id, user_id, price)
VALUES 
(1, 1, 15000),
(2, 2, 165000);


-- Запросы (потом вынести в отдельный файл).

-- Получить все категории.
SELECT * FROM stuff_categories; 
-- если только названия
SELECT name FROM stuff_categories;


-- Получить самые новые, открытые лоты. 
-- Каждый лот должен включать название, стартовую цену, ссылку на изображение, цену, название категории;

-- цену... какую цену?
SELECT l.name, l.start_price, l.image_url, l.date_creation, l.date_end, cat.name as 'category', l.description FROM lots as l
LEFT JOIN stuff_categories as cat on l.id_category = cat.id
WHERE date_end IS NOT NULL AND date_end > NOW()
ORDER BY date_creation DESC;

-- Показать лот по его ID
-- какие поля показать?
SELECT l.name, l.start_price, l.date_creation, l.date_end, l.step_bet, us.name as 'author', cat.name as 'category' FROM lots as l
LEFT JOIN stuff_categories as cat on l.id_category = cat.id
LEFT JOIN users as us on l.id_author = us.id
WHERE l.id = 1; -- тут указать id лота


-- Обновить название лота по его id
UPDATE lots SET name = 'new lot name'
WHERE id = 1; -- тут указать id нужного лота


-- Получить список самых свежих ставок для лота по его идентификатору.
-- самых свежих... это значит первые 3, 5, 10 ?
SELECT b.create_date, b.price, us.name as 'bet creator', l.name as 'lot name' FROM bets
LEFT JOIN users as us on b.user_id = us.id
LEFT JOIN lots as l in b.lot_id = l.id
WHERE lot_id = 1 -- тут указать id лота
ORDER BY create_date DESC 
LIMIT 3;
