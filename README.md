## Пример преобразования видео под формат передачи протоколом HLS

### Инструкция по использованию
1. Установить зависимости composer
```bash
composer install
```
2. Применить миграции
```bash
php artisan migrate
```
3. Для PHP должна быть установлена библиотека **Ffmpeg**
4. Создать сиволическую ссылку:
```bash
php artisan storage:link
```
___

Контроллер: **app/Http/Controllers/ContentController.php**

Job: **app/Jobs/VideoContentToHlcConvertJob.php**

Веб-интерфейс: **resources/views/welcome.blade.php**

- Очереди работают через БД
- Кэширование происходит в файлы