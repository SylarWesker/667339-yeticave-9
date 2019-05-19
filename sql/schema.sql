-- Создание БД.
CREATE DATABASE IF NOT EXISTS yeticave
	DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE yeticave;

-- Таблица категорий.
CREATE TABLE IF NOT EXISTS stuff_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50), -- название
    symbol_code VARCHAR(50) -- Символьный код нужен, чтобы назначить правильный класс в меню категорий.
);

-- Таблица пользователей.
CREATE TABLE IF NOT EXISTS user (
	id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    avatar_url VARCHAR(255),
    contacts VARCHAR(255) -- контакты
);

-- Таблица лотов.
CREATE TABLE IF NOT EXISTS lot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    image_url VARCHAR(255),
    start_price DOUBLE NOT NULL,
    end_date DATETIME, -- дата завершения "действия лота"
    step_bet DOUBLE DEFAULT '0.0000',  -- шаг ставки
   
    author_id INT NOT NULL, -- id пользователя, создавшего лот
    CONSTRAINT lot_userAuthor_fk
    FOREIGN KEY (author_id) REFERENCES user(id),

    winner_id INT, -- id победителя
    CONSTRAINT lot_userWinner_fk
    FOREIGN KEY (winner_id) REFERENCES user(id),

    category_id INT NOT NULL, -- id категории объявления/спорт инвентаря
    CONSTRAINT lot_category_fk
    FOREIGN KEY (category_id) REFERENCES stuff_category(id)
);

-- Добавление полнотекстного индекса для поиска лота по их названию и описанию.
CREATE FULLTEXT INDEX lot_name_desc_search
on lot(name, description)

-- Таблица ставок.
CREATE TABLE IF NOT EXISTS bet ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    create_date DATETIME DEFAULT CURRENT_TIMESTAMP, 
    price DOUBLE NOT NULL DEFAULT '0.0000', -- цена, по которой пользователь готов приобрести лот. 

    user_id INT NOT NULL, 
    CONSTRAINT bet_user_fk 
    FOREIGN KEY (user_id) REFERENCES user(id), 
    
    lot_id INT, 
    CONSTRAINT bet_lot_fk FOREIGN KEY (lot_id) REFERENCES lot(id) 
);

-- Добавить уникальный индекс на имя пользователя и его почту
