# Ответ на контрольный вопрос №12

**Студент:** [Лысенко Валерий Валерьевич]  
**Группа:** [454]  
**Вариант:** 12  
**Дата:** 20.05.2026

## Формулировка вопроса

Как обеспечить целостность данных при работе с кредитными историями клиентов в системе онлайн-записи в банк? Опишите механизмы ограничений на уровне СУБД и прикладной логики.

## Развёрнутый ответ

В системе онлайн-записи в банк (вариант 12) целостность данных кредитных историй обеспечивается многоуровневой системой ограничений.

### 1. Ограничения на уровне СУБД (MySQL)

В таблице `credit_histories` реализованы следующие CHECK-ограничения:

```sql
CREATE TABLE credit_histories (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL UNIQUE,
    credit_score INT NOT NULL CHECK (credit_score BETWEEN 300 AND 850),
    has_defaults BOOLEAN DEFAULT FALSE,
    total_loans INT DEFAULT 0 CHECK (total_loans >= 0),
    last_update DATE NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE
);
