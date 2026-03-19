# Todo 앱 설치 및 실행 가이드

## 빌드 결과

| 항목 | 상태 |
|------|------|
| Apache 2.4.58 | ✅ 실행 중 |
| PHP 8.3.6 (mysqli 확장) | ✅ 확인 완료 |
| MySQL 8.0.45 | ✅ 실행 중 |
| PHP 구문 검사 (5개 파일) | ✅ 에러 없음 |
| Apache PHP 모듈 (php8.3) | ✅ 활성화됨 |
| `/var/www/html/todo` 배포 | ✅ 완료 |

---

## 해결 필요 항목 (수동 설정)

### 문제: MySQL root 비밀번호 미설정

자동 실행 환경에서 MySQL root 계정의 비밀번호를 알 수 없어 DB 초기화를 자동화할 수 없었습니다.
아래 두 단계를 직접 실행해 주세요.

---

## 1단계: DB 초기화

터미널에서 아래 명령을 실행하세요.

```bash
mysql -u root -p < /home/lsj/project/setup.sql
```

비밀번호 입력 프롬프트가 나오면 MySQL root 비밀번호를 입력합니다.

> **root 비밀번호가 없는 경우 (Ubuntu 기본)**
> ```bash
> sudo mysql < /home/lsj/project/setup.sql
> ```

---

## 2단계: db.php 비밀번호 수정

`/var/www/html/todo/db.php` 파일의 `ENTER_YOUR_PASSWORD` 부분을 실제 비밀번호로 변경합니다.

```bash
nano /var/www/html/todo/db.php
```

변경 전:
```php
$password = 'ENTER_YOUR_PASSWORD';
```

변경 후 (예시):
```php
$password = '실제비밀번호';
```

> **root 비밀번호 없이 socket 인증을 사용하는 경우**
>
> DB 연결을 socket 방식으로 변경하거나, 전용 MySQL 계정을 생성하는 것을 권장합니다.
>
> ```sql
> -- MySQL에서 실행
> CREATE USER 'todo_user'@'localhost' IDENTIFIED BY 'your_password';
> GRANT ALL PRIVILEGES ON todo_db.* TO 'todo_user'@'localhost';
> FLUSH PRIVILEGES;
> ```
>
> 이후 db.php에서 user를 `todo_user`, password를 `your_password`로 설정하세요.

---

## 3단계: 브라우저 접속

```
http://localhost/todo
```

---

## 파일 목록

```
/home/lsj/project/
├── project.md      # 프로젝트 문서 (Mermaid 흐름도 포함)
├── setup.sql       # DB 초기화 스크립트
└── readme.md       # 이 파일 (설치 가이드)

/var/www/html/todo/
├── index.php       # 메인 페이지 (목록 조회)
├── db.php          # MySQL 연결 설정
├── add.php         # 할 일 추가
├── complete.php    # 완료 토글
├── delete.php      # 삭제
└── style.css       # UI 스타일
```
