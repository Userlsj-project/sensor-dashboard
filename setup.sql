USE sensor_db;

DROP TABLE IF EXISTS sensor_data;

CREATE TABLE sensor_data (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id   VARCHAR(20),
    temperature FLOAT,
    humidity    FLOAT,
    pressure    FLOAT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO sensor_data (sensor_id, temperature, humidity, pressure) VALUES
('SENSOR-01', 22.5, 55.0, 1013.2),
('SENSOR-02', 28.3, 62.1, 1010.5),
('SENSOR-03', 25.7, 48.9, 1015.8),
('SENSOR-01', 23.1, 57.4, 1012.9),
('SENSOR-02', 29.6, 64.3, 1011.1),
('SENSOR-03', 24.4, 50.2, 1014.6),
('SENSOR-01', 21.8, 53.7, 1013.7),
('SENSOR-02', 27.9, 61.8, 1010.2),
('SENSOR-03', 26.2, 47.5, 1016.3),
('SENSOR-01', 22.0, 56.1, 1012.4);
