import mysql.connector
import random
import time
import datetime

SENSORS = ['SENSOR-01', 'SENSOR-02', 'SENSOR-03']

def get_connection():
    return mysql.connector.connect(
        host='localhost',
        user='sensor_user',
        password='Sensor@1234',
        database='sensor_db'
    )

def insert_reading(cursor, sensor_id, temp, humidity, pressure):
    sql = """
        INSERT INTO sensor_data (sensor_id, temperature, humidity, pressure)
        VALUES (%s, %s, %s, %s)
    """
    cursor.execute(sql, (sensor_id, temp, humidity, pressure))

def main():
    print("Starting sensor injector. Press Ctrl+C to stop.")
    conn = get_connection()
    cursor = conn.cursor()
    index = 0

    try:
        while True:
            sensor_id = SENSORS[index % len(SENSORS)]
            temp = round(random.uniform(20.0, 35.0), 2)
            humidity = round(random.uniform(40.0, 80.0), 2)
            pressure = round(random.uniform(1000.0, 1025.0), 2)

            insert_reading(cursor, sensor_id, temp, humidity, pressure)
            conn.commit()

            print(f"Inserted: {sensor_id} temp={temp:.1f} humidity={humidity:.1f} pressure={pressure:.1f}")

            index += 1
            time.sleep(3)

    except KeyboardInterrupt:
        print("\nStopped by user.")
    finally:
        cursor.close()
        conn.close()
        print("Database connection closed.")

if __name__ == '__main__':
    main()
