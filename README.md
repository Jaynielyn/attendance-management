# Attendance-Management/勤怠管理アプリ

## Dockerビルド
1.git@github.com:Jaynielyn/attendance-management.git
2.$ docker-compose up -d --build

## 環境構築
1.docker-compose exec php bash
2.composer install
3.cp .env.example .env
4..env.exampleファイルから.envを作成し、環境変数を変更
5.php artisan key:generate
6.php artisan migrate
7.php artisan db:seed

## 使用技術
Laravel Framework 8.83.29

## URL
環境開発:http://localhost
phpMyAdmin:http://localhost:8080/

## ER図
以下は本プロジェクトのER図です。

![ER図](public/images/ER.png)

## 備考
phpunitを行う際は一度メール認証設定がない状態で行う。
