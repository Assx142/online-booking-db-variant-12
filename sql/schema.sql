-- ============================================================================
-- Вариант 12: Система онлайн-записи в банк
-- Сущности: Клиенты, Услуги, Сотрудники, Отделы, Должности, Кредитные истории
-- ============================================================================

DROP DATABASE IF EXISTS online_bank_variant12;
CREATE DATABASE online_bank_variant12 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;
USE online_bank_variant12;

-- ============================
-- СПРАВОЧНИКИ
-- ============================

-- Отделы банка
CREATE TABLE departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Должности сотрудников
CREATE TABLE positions (
    position_id INT AUTO_INCREMENT PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL UNIQUE,
    min_salary DECIMAL(10,2) CHECK (min_salary >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Типы банковских услуг
CREATE TABLE service_types (
    type_id INT AUTO_INCREMENT PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

-- Банковские услуги
CREATE TABLE services (
    service_id INT AUTO_INCREMENT PRIMARY KEY,
    service_name VARCHAR(150) NOT NULL UNIQUE,
    type_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL CHECK (price >= 0),
    duration_minutes INT NOT NULL CHECK (duration_minutes BETWEEN 5 AND 240),
    min_deposit_amount DECIMAL(12,2) DEFAULT 0 CHECK (min_deposit_amount >= 0),
    description TEXT,
    FOREIGN KEY (type_id) REFERENCES service_types(type_id) ON DELETE RESTRICT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- КЛИЕНТЫ И КРЕДИТНАЯ ИСТОРИЯ
-- ============================

-- Клиенты банка
CREATE TABLE clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    patronymic VARCHAR(50),
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    passport_series CHAR(4) NOT NULL,
    passport_number CHAR(6) NOT NULL,
    birth_date DATE NOT NULL,
    registration_address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (birth_date <= CURDATE() - INTERVAL 18 YEAR),
    CHECK (LENGTH(passport_series) = 4 AND passport_series REGEXP '^[0-9]{4}$'),
    CHECK (LENGTH(passport_number) = 6 AND passport_number REGEXP '^[0-9]{6}$')
) ENGINE=InnoDB;

-- Кредитные истории клиентов (ключевая таблица варианта 12!)
CREATE TABLE credit_histories (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL UNIQUE,
    credit_score INT NOT NULL CHECK (credit_score BETWEEN 300 AND 850),
    has_defaults BOOLEAN DEFAULT FALSE,
    total_loans INT DEFAULT 0 CHECK (total_loans >= 0),
    last_update DATE NOT NULL,
    notes TEXT,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================
-- СОТРУДНИКИ БАНКА
-- ============================

-- Сотрудники
CREATE TABLE employees (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    last_name VARCHAR(50) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    patronymic VARCHAR(50),
    position_id INT NOT NULL,
    department_id INT NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    hire_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (position_id) REFERENCES positions(position_id) ON DELETE RESTRICT,
    FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE RESTRICT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (hire_date <= CURDATE())
) ENGINE=InnoDB;

-- График работы сотрудников (расписание приёма)
CREATE TABLE employee_schedule (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    work_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_slot (employee_id, work_date, start_time),
    CHECK (end_time > start_time)
) ENGINE=InnoDB;

-- ============================
-- ЗАПИСИ НА ОБСЛУЖИВАНИЕ
-- ============================

-- Записи клиентов
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    employee_id INT NOT NULL,
    service_id INT NOT NULL,
    appointment_datetime DATETIME NOT NULL,
    status ENUM('запланировано', 'в_процессе', 'завершено', 'отменено', 'отказано') DEFAULT 'запланировано',
    deposit_amount DECIMAL(12,2) DEFAULT 0 CHECK (deposit_amount >= 0),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE RESTRICT,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE RESTRICT,
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE RESTRICT,
    UNIQUE KEY unique_time_slot (employee_id, appointment_datetime),
    CHECK (appointment_datetime >= NOW())
) ENGINE=InnoDB;

-- ============================
-- ИНДЕКСЫ ДЛЯ ОПТИМИЗАЦИИ
-- ============================

CREATE INDEX idx_clients_phone ON clients(phone);
CREATE INDEX idx_clients_email ON clients(email);
CREATE INDEX idx_appointments_datetime ON appointments(appointment_datetime);
CREATE INDEX idx_appointments_client ON appointments(client_id);
CREATE INDEX idx_appointments_employee ON appointments(employee_id);
CREATE INDEX idx_employees_department ON employees(department_id);
