Работа с кейсами
================

Класс CallCases
---------------

**Инициализация объекта:**

```php

$callCases = new CallCases('http://***.**.**.**/fx/api', 'username', 'password');
$callCases->setProjectUuid('projectuiid');

```

**Получение списка кейсов**

```php

$callCases->getList(); //Первые 100

$callCases->getList([], true); //Полный список

$cases = $callCases->getList(['state' => 'adjourned']); //Фильтр по статусу

echo $cases['count']; //Количество

foreach($cases['callcases'] as $case) { //Список кейсов
    $echo $case['uuid'];
}

```

**Создание, чтение, обновление и удаление кейсов**

```php

$newUuid = $callCases->create([
            'title' => 'Контакт123456',
            'comment' => 'Тестовый',
            'phoneNumbers' => [
                                'phoneNumber' => [
                                    [
                                        'value' => '8000000000',
                                        'phoneNumberType' => 'HOME',
                                    ]
                                ]
                            ]
        ]);

$case = $callCases->get($newUiid);

echo $case['title']; //Контакт123456

$case['comment'] = 'Изменен';

$update = $callCases->update($case);

$callCases->delete($case['uuid']);

```

**Изменение состояния кейса**

```php

$state = $callCases->getState($newUiid); //'new';

$callCases->setState($newUiid, ['state' => 'adjourned']);

```

**Операции с использованием внешнего идентификатора(Создание, чтение, обновление и удаление кейсов)**

Обязательно указывать id.

```php

$newUuid = $callCases->createWithId([
            'id' => 'project.123456.567890',
            'title' => 'Контакт123456',
            'comment' => 'Тестовый',
            'phoneNumbers' => [
                                'phoneNumber' => [
                                    [
                                        'value' => '8000000000',
                                        'phoneNumberType' => 'HOME',
                                    ]
                                ]
                            ]
        ]);

$case = $callCases->getByExtId('project.123456.567890');

echo $case['title']; //Контакт123456

$case['comment'] = 'Изменен';

$update = $callCases->updateByExtId($case);

$callCases->deleteByExtId('project.123456.567890');

```

**Массовые операции с кейсами**

```php

$callCases->createList($cases);

$callCases->updateList($cases);

$callCases->deleteList($cases);

```

**Формат данных кейса**

Подробнее: https://callcenter.naumen.ru/docs/ru/np63/ncc/web/ncc.htm#Integration Capabilities/REST_API/Cases/Case_Data_Format.htm%3FTocPath%3D%25D0%2598%25D0%25BD%25D1%2582%25D0%25B5%25D0%25B3%25D1%2580%25D0%25B0%25D1%2586%25D0%25B8%25D0%25BE%25D0%25BD%25D0%25BD%25D1%258B%25D0%25B5%2520%25D0%25B2%25D0%25BE%25D0%25B7%25D0%25BC%25D0%25BE%25D0%25B6%25D0%25BD%25D0%25BE%25D1%2581%25D1%2582%25D0%25B8%7CREST%2520API%7CREST%2520API%2520%25D0%25B4%25D0%25BB%25D1%258F%2520%25D0%25BA%25D0%25B5%25D0%25B9%25D1%2581%25D0%25BE%25D0%25B2%7C_____6

**Формат списка кейсов**

Подробнее: https://callcenter.naumen.ru/docs/ru/np63/ncc/web/ncc.htm#Integration Capabilities/REST_API/Cases/Cases_List_Format.htm%3FTocPath%3D%25D0%2598%25D0%25BD%25D1%2582%25D0%25B5%25D0%25B3%25D1%2580%25D0%25B0%25D1%2586%25D0%25B8%25D0%25BE%25D0%25BD%25D0%25BD%25D1%258B%25D0%25B5%2520%25D0%25B2%25D0%25BE%25D0%25B7%25D0%25BC%25D0%25BE%25D0%25B6%25D0%25BD%25D0%25BE%25D1%2581%25D1%2582%25D0%25B8%7CREST%2520API%7CREST%2520API%2520%25D0%25B4%25D0%25BB%25D1%258F%2520%25D0%25BA%25D0%25B5%25D0%25B9%25D1%2581%25D0%25BE%25D0%25B2%7C_____7

