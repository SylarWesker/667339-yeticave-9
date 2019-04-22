-- Создание БД.
CREATE DATABASE yeticave
	DEFAULT CHARACTER SET utf8
    DEFAULT COLLATE utf8_general_ci;

USE yeticave;

-- Таблица категорий.
CREATE TABLE stuff_categories(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50), -- название
    symbol_code VARCHAR(50) -- Символьный код нужен, чтобы назначить правильный класс в меню категорий.
);

-- Таблица пользователей.
CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    avatar_url VARCHAR(255),
    contacts VARCHAR(255) -- контакты
);

-- Таблица лотов.
CREATE TABLE lots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    image_url VARCHAR(255),
    start_price DECIMAL(8, 2),
    date_end TIMESTAMP, -- дата завершения "действия лота"
    step_bet DECIMAL(8, 2),  -- шаг ставки
   
    id_author INT NOT NULL, -- id пользователя, создавшего лот
    CONSTRAINT lots_usersAuthor_fk
    FOREIGN KEY (id_author) REFERENCES users(id),

    id_winner INT, -- id победителя
    CONSTRAINT lots_usersWinner_fk
    FOREIGN KEY (id_winner) REFERENCES users(id),

    id_category INT NOT NULL, -- id категории объявления/спорт инвентаря
    CONSTRAINT lots_categories_fk
    FOREIGN KEY (id_category) REFERENCES stuff_categories(id)
);

-- Таблица ставок.
CREATE TABLE bets ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    create_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    price DECIMAL(8, 2), -- цена, по которой пользователь готов приобрести лот. 

    user_id INT NOT NULL, 
    CONSTRAINT bets_users_fk 
    FOREIGN KEY (user_id) REFERENCES users(id), 
    
    lot_id INT, 
    CONSTRAINT bets_lots_fk FOREIGN KEY (lot_id) REFERENCES lots(id) 
);
