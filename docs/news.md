Управление новостями
====================

Класс News
----------

```php

$news = new News('http://***.**.**.**/fx/api', 'username', 'password');
$news->setProjectUuid('projectuiid');

// Добавление
$new = [
    'content' => 'Новость',
    'importance' => News::importanceHigh
];

$id = $news->create($new);

// Изменение
$new2 = [
    'uuid' => $id,
    'content' => 'Новость изменена',
    'importance' => News::IMPORTANCE_NORMAL
];

$news->update($new2);

// Удаление
$news->delete($id);

// Получение списка
$news->getList([], true);

```