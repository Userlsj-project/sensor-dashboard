# 스마트 팩토리 가상 센서 모니터링 시스템

Ubuntu 24.04 환경에서 LAMP 스택과 Python Flask로 구축한 가상 IoT 센서 모니터링 대시보드입니다.
물리적인 하드웨어 없이 Python 시뮬레이터가 센서 데이터를 생성하며, 웹 브라우저에서 실시간으로 확인할 수 있습니다.

---

## 주요 기능

- 온도, 습도, 기압 데이터를 3초마다 가상으로 생성
- PHP + Chart.js 대시보드에서 실시간 시각화 (5초 자동 새로고침)
- Flask REST API를 통한 JSON 형식 데이터 제공
- MySQL 8.0 데이터베이스 연동
- 3개의 가상 센서: SENSOR-01, SENSOR-02, SENSOR-03

---

## 사전 요구사항

- Ubuntu 24.04 (또는 Zorin OS / VMware 환경)
- LAMP 스택: Apache2, MySQL 8.0, PHP 8.3
- Python 3.x

---

## 설치 방법

### 1. 시스템 패키지 설치
```bash
sudo apt update
sudo apt install -y apache2 mysql-server php php-mysqli libapache2-mod-php python3 python3-pip
```

### 2. Python 및 PHP 의존성 설치
```bash
./install.sh
```

### 3. 데이터베이스 설정
```bash
chmod +x db_setup.sh
./db_setup.sh
mysql -u sensor_user -pSensor@1234 sensor_db < setup.sql
```

### 4. PHP 대시보드 배포
```bash
sudo cp index.php /var/www/html/index.php
sudo systemctl restart apache2
```

---

## 실행 방법

**터미널 1 — 가상 센서 데이터 주입기 실행:**
```bash
python3 injector.py
```

**터미널 2 — Flask API 서버 실행:**
```bash
python3 api.py
```

**브라우저에서 접속:**
- 대시보드: http://localhost/
- API 전체 데이터 (최근 20건): http://localhost:5000/api/data
- API 센서별 최신값: http://localhost:5000/api/latest
- API 상태 확인: http://localhost:5000/api/health

---

## 파일 구조

```
.
├── db_setup.sh     # MySQL 사용자 및 데이터베이스 생성
├── install.sh      # 의존성 패키지 설치
├── setup.sql       # 테이블 생성 및 샘플 데이터 삽입
├── injector.py     # 가상 센서 데이터 생성기
├── api.py          # Flask REST API 서버
├── index.php       # PHP 대시보드 (Apache)
├── process.md      # 구축 과정 및 아키텍처 문서
└── README.md       # 프로젝트 안내 (이 파일)
```

---

## GitHub

[https://github.com/YOUR_USERNAME/sensor-dashboard](https://github.com/YOUR_USERNAME/sensor-dashboard)
