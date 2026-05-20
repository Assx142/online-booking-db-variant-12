-- ============================================================================
-- Запрос для варианта 12: 
-- "Сотрудники, которые обслужили наибольшее количество клиентов за месяц"
-- ============================================================================

-- Параметры: заменить :target_month на нужную дату, например '2026-05-01'
SET @target_month = '2026-05-01';

SELECT 
    e.employee_id,
    CONCAT(e.last_name, ' ', e.first_name, ' ', IFNULL(e.patronymic, '')) AS employee_full_name,
    p.position_name AS position,
    d.department_name AS department,
    COUNT(DISTINCT a.client_id) AS unique_clients_count,
    COUNT(a.appointment_id) AS total_appointments,
    ROUND(AVG(s.price), 2) AS avg_service_price
FROM employees e
JOIN positions p ON e.position_id = p.position_id
JOIN departments d ON e.department_id = d.department_id
JOIN appointments a ON e.employee_id = a.employee_id
JOIN services s ON a.service_id = s.service_id
WHERE a.status IN ('завершено', 'в_процессе')
  AND a.appointment_datetime >= @target_month
  AND a.appointment_datetime < @target_month + INTERVAL 1 MONTH
GROUP BY e.employee_id, p.position_name, d.department_name
HAVING unique_clients_count > 0
ORDER BY unique_clients_count DESC, total_appointments DESC
LIMIT 10;
