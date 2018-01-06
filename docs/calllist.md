Управление состояние обзвона
============================

Класс CallList
--------------

```php
$callList = new CallList('http://***.**.**.**/fx/api', 'username', 'password');
$callList->setProjectUuid('projectuiid');

$callList->start(); //Запуск
$callList->stop(); //Остановка
$callList->update(); //Обновление контактов
$callList->delete(); //Удаление

```