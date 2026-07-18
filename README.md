# WB_api test task
Импорт данных по внешнему АПИ, сохранение в БД. Удаленная БД на хостинге Aiven.

Реализована идемпотентность при загрузке данных (перед импортом из БД вычищаются данные за указанный период, чтобы обеспечить дедупликацию строк), для Складов всегда импортируются данные за сегодняшний день (timezone Europe/Moscow). 

Реализована загрузка батчами для обеспечения корректной выгрузки данных при ограниченных лимитах на загрузку.

# Данные для подключения к БД
```
DB_CONNECTION=mysql
DB_HOST=164.92.145.216
DB_PORT=23641
DB_DATABASE=defaultdb
DB_USERNAME=avnadmin
DB_PASSWORD=AVNS_40cpC-4oGATXq4fBS--
DB_ROOT_PASSWORD=AVNS_40cpC-4oGATXq4fBS--
DB_FORWARD_PORT=3307
```

# Названия таблиц
- stocks
- orders
- incomes
- sales

# Команды
Сборка докер-контейнера
```
docker compose up -d
```
Запуск импорта
```
docker compose exec app php artisan import:all-data --date-from=[Y-m-d] --date-to=[Y-m-d]
```
Пример просмотра содержимого таблиц 
```
docker compose exec app php artisan tinker
```
```
foreach (['incomes','orders','sales','stocks'] as $t) {
          echo PHP_EOL."=== $t (total ".DB::table($t)->count().") ===".PHP_EOL;
          }
``` 
<- кол-во записей в каждой таблице
