-- Создание БД.

-- Таблица категорий.
CREATE TABLE stuff_categories(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50), -- название
    symbol_code VARCHAR(50) -- Символьный код нужен, чтобы назначить правильный класс в меню категорий.
);

-- Таблица лотов.
-- ToDo!!! Добавить внешние ключи
CREATE TABLE lots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name varchar(255) NOT NULL,
    description varchar(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image_url varchar(255),
    start_price decimal(8, 2),
    date_end TIMESTAMP, -- дата завершения
    step_bet decimal(8, 2),  -- шаг ставки
   
    id_author INT NOT NULL, -- id пользователя, создавшего лот
    id_winner INT, -- id победителя
    id_category INT -- id категории объявления/спорт инвентаря
);

