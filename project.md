# 스마트 팩토리 가상 센서 모니터링 시스템

## 프로젝트 개요

Ubuntu 24.04 (VMware) 환경에서 LAMP 스택과 Python Flask를 기반으로 구축한 가상 IoT 모니터링 대시보드입니다.
물리적인 하드웨어 장비 없이, `injector.py`가 센서 데이터를 가상으로 생성하여 실시간 모니터링 환경을 시뮬레이션합니다.

---

## 주요 파일 설명

### injector.py
- 온도(temperature), 습도(humidity), 기압(pressure) 값을 무작위로 생성하는 가상 센서 시뮬레이터입니다.
- 3개의 센서(`SENSOR-01`, `SENSOR-02`, `SENSOR-03`)를 순환하며 3초마다 MySQL에 데이터를 삽입합니다.
- `mysql-connector-python`을 사용하여 `sensor_db` 데이터베이스에 직접 연결합니다.
- 터미널에 삽입된 데이터를 실시간으로 출력하며, `Ctrl+C`로 안전하게 종료할 수 있습니다.

### api.py
- Python Flask로 작성된 REST API 서버로, 포트 5000에서 실행됩니다.
- `flask-cors`를 적용하여 외부 요청을 허용합니다.
- 제공 엔드포인트:
  - `GET /api/data` — 최근 20건의 센서 데이터를 JSON 형식으로 반환
  - `GET /api/latest` — 센서별 최신 데이터 1건씩을 JSON 형식으로 반환
  - `GET /api/health` — 서버 상태 확인 (`{"status": "ok"}`)
- `mysql-connector-python`으로 MySQL에 연결하여 데이터를 조회합니다.

### index.php
- Apache2를 통해 제공되는 PHP 8.3 기반의 실시간 웹 대시보드입니다.
- `mysqli_connect()`로 MySQL에 연결하여 최근 50건의 센서 데이터를 조회합니다.
- 5초마다 페이지를 자동 새로고침(`<meta http-equiv="refresh" content="5">`)하여 최신 데이터를 표시합니다.
- 화면 구성:
  - 센서별 최신값 요약 테이블 (MAX(id) 서브쿼리 활용)
  - Chart.js 라인 차트: 시간 흐름에 따른 온도 변화 (SENSOR-01 빨강, SENSOR-02 파랑, SENSOR-03 초록)
  - 최근 50건 전체 센서 데이터 테이블

### setup.sql
- `sensor_db` 데이터베이스에서 `sensor_data` 테이블을 생성하는 SQL 스크립트입니다.
- 기존 테이블이 있으면 삭제 후 재생성합니다(`DROP TABLE IF EXISTS`).
- 초기 샘플 데이터 10건을 삽입하여 대시보드를 바로 확인할 수 있도록 합니다.
- 테이블 구조: `id`, `sensor_id`, `temperature`, `humidity`, `pressure`, `recorded_at`

---

## 기술 스택

| 구분 | 기술 |
|------|------|
| 운영체제 | Ubuntu 24.04 (Zorin OS, VMware) |
| 웹 서버 | Apache2 |
| 데이터베이스 | MySQL 8.0 |
| 백엔드 API | Python Flask + mysql-connector-python |
| 프론트엔드 | PHP 8.3 + Chart.js (CDN) |
| 데이터 생성기 | Python 3 — injector.py |

---

## 시스템 흐름

```
injector.py  →  MySQL (sensor_db)  →  index.php / Apache  →  브라우저 (5초 자동 새로고침)
injector.py  →  MySQL (sensor_db)  →  api.py / Flask :5000  →  브라우저 (JSON 응답)
```

1. `injector.py`가 3초마다 가상 센서 데이터를 생성하여 MySQL `sensor_data` 테이블에 삽입합니다.
2. 브라우저에서 `http://localhost/`에 접속하면 Apache가 `index.php`를 실행합니다.
3. `index.php`는 MySQL에서 최근 50건의 데이터를 조회하여 Chart.js 차트와 테이블로 렌더링합니다.
4. 페이지는 5초마다 자동으로 새로고침되어 최신 센서 값을 반영합니다.
5. `http://localhost:5000/api/data`에 접속하면 Flask API가 JSON 형식으로 데이터를 반환합니다.

---

## 데이터베이스 구조

```sql
CREATE TABLE sensor_data (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    sensor_id   VARCHAR(20),        -- 센서 식별자 (SENSOR-01 ~ SENSOR-03)
    temperature FLOAT,              -- 온도 (°C)
    humidity    FLOAT,              -- 습도 (%)
    pressure    FLOAT,              -- 기압 (hPa)
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP  -- 기록 시각
);
```
