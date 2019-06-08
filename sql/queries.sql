-- Добавление данных.
USE yeticave;

-- Добавление категорий.
INSERT INTO stuff_category (name, symbol_code) 
VALUES
('Доски и лыжи', 'boards'), 
('Крепления',    'attachment'),
('Ботинки',      'boots'), 
('Одежда',       'clothing'), 
('Инструменты',  'tools'), 
('Разное',       'other');

-- Добавление пользователей.
INSERT INTO user (name, password, email, contacts)
VALUES
('admin',       'hashed_password', 'hackerman@gmail.com', 'Top secret info'),
('simple_user', 'hashed_password', 'vasya2005@mail.ru',   'New-York city честно');

-- Добавление объявлений.
INSERT INTO lot (author_id, category_id, name, start_price, image_url, step_bet, end_date)
VALUES
(1, 1, '2014 Rossignol District Snowboard',                 10999,      'img/lot-1.jpg', 500,   NOW() + INTERVAL 7 day),
(1, 1, 'DC Ply Mens 2016/2017 Snowboard',                   159999,     'img/lot-2.jpg', 1000,  NOW() + INTERVAL 7 day),
(1, 2, 'Крепления Union Contact Pro 2015 года размер L/XL', 8000,       'img/lot-3.jpg', 100,   NOW() + INTERVAL 7 day),
(2, 3, 'Ботинки для сноуборда DC Mutiny Charocal',          10999,      'img/lot-4.jpg', 500,   NOW() + INTERVAL 7 day),
(2, 4, 'Куртка для сноуборда DC Mutiny Charocal',           7500,       'img/lot-5.jpg', 100,   NOW() + INTERVAL 7 day),
(2, 6, 'Маска Oakley Canopy',                               5400,       'img/lot-6.jpg', 100,   NOW() + INTERVAL 7 day);

-- Добавление ставок.
INSERT INTO bet (lot_id, user_id, price)
VALUES 
(1, 1, 15000),
(2, 2, 165000);
