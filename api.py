from flask import Flask, jsonify
from flask_cors import CORS
import mysql.connector

app = Flask(__name__)
CORS(app)

DB_CONFIG = {
    'host': 'localhost',
    'user': 'sensor_user',
    'password': 'Sensor@1234',
    'database': 'sensor_db'
}

def get_connection():
    return mysql.connector.connect(**DB_CONFIG)

@app.route('/api/data', methods=['GET'])
def get_data():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT id, sensor_id, temperature, humidity, pressure,
               recorded_at
        FROM sensor_data
        ORDER BY recorded_at DESC
        LIMIT 20
    """)
    rows = cursor.fetchall()
    cursor.close()
    conn.close()
    for row in rows:
        row['recorded_at'] = str(row['recorded_at'])
    return jsonify(rows)

@app.route('/api/latest', methods=['GET'])
def get_latest():
    conn = get_connection()
    cursor = conn.cursor(dictionary=True)
    cursor.execute("""
        SELECT s.id, s.sensor_id, s.temperature, s.humidity, s.pressure, s.recorded_at
        FROM sensor_data s
        INNER JOIN (
            SELECT sensor_id, MAX(id) AS max_id
            FROM sensor_data
            GROUP BY sensor_id
        ) latest ON s.sensor_id = latest.sensor_id AND s.id = latest.max_id
        ORDER BY s.sensor_id
    """)
    rows = cursor.fetchall()
    cursor.close()
    conn.close()
    for row in rows:
        row['recorded_at'] = str(row['recorded_at'])
    return jsonify(rows)

@app.route('/api/health', methods=['GET'])
def health():
    return jsonify({"status": "ok"})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
