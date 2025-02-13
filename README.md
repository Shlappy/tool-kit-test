# Тестовое задание для Tool-Kit

### Инструкция

1. Клонировать репозиторй
```bash
git clone https://github.com/Shlappy/tool-kit-test.git tool-kit
```

2. Собрать, поднять все контейнеры и запустить сервер:
```bash
make build-start-prod
```

3. Готово, теперь можно использовать приложение. Для этого нужно перейти по стандартному адресу:
```bash
http://localhost
```
Если нужно сменить порт, можно сделать это в .env файле, ключ WEB_PORT_HTTP. Тогда нужно зайти по адресу:
```bash
http://localhost:port
```
где port - уканный в .env порт.

#### Для входа за админа:
Логин - admin@mail.ru<br>
Пароль - admin

---
### Справка
Для остановки всех контейнеров:
```bash
make stop-prod
```

Для запуска без сборки ввести команду:
```bash
make start-prod
```