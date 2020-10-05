#Safedeal Trainee API
##Описание 
Это реализация API сервиса для работы с заказами. Данный API взаимодействует с клиентом посредством REST HTTP запросов. 
Все реализованные методы описаны согласно спецификации OpenAPI. 

В работе сервиса подразумевается взаимодействия в двумя другими сервисами: products и logistic. 
От этих сервисов в процессе работы исходный сервис получает дополнительную информацию, отправляя на них необходимые HTTP запросы. 
Их url адреса определяются переменными окружения `PRODUCTS_API_URL` и `LOGISTIC_API_URL` соответственно. 
В данной реализации их работу имитирует mock-сервер, который возвращает заранее подготовленные ответы на запросы.

Кэширование и RateLimit реализованы с использование Redis.
Данные хранятся в СУБД PostgreSQL.

##Начало работы
####Создание и заполнение файлов переменных окружения
Переименуйте `.env.dist` и `.env.test.dist` в `.env` и `.env.test` соответственно.
Заметите `/YOUR/HOME/` в переменных окружения *COMPOSER_HOME* и *COMPOSER_CACHE_DIR* в обоих вышеназванных файлах 
на абсолютный адрес вашего домашнего каталога.
####Установка пакетов
```bash
docker-compose run --rm  composer
```

##Запуск
####Выполнение миграций и загрузка фиктивных данных в БД
```bash
docker-compose run --rm  migrations-and-fixtures
```

####Запуск mock-сервера
```bash
docker-compose run --rm  --service-ports mock-server
```

####Запуск
```bash
docker-compose up --remove-orphans -d nginx
```
После выполнения команды проект будет доступен по адресу [safedeal.localhost](http://safedeal.localhost)
####Запуск swagger-клиента
```bash
docker-compose run --rm  --service-ports swagger
```
После выполнения команды swagger-клиент будет запущен по адресу [127.0.0.1:8080](http://127.0.0.1:8080)

##Тестирование
####Выполнение миграций и заполнение БД фиктивными данными для тестового окружения
```bash
docker-compose -f docker-compose.test.yaml run --rm migrations-and-fixtures
```
####Запуск тестов
```bash
docker-compose -f docker-compose.test.yaml run --rm phpunit
```