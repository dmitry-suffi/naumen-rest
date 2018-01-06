<?php

namespace suffi\naumenRest\parser;

/**
 * Абстрактный класс для парсинга данных
 * Class Parser
 * @package suffi\naumenRest\parser
 */
abstract class Parser
{
    /**
     * Преобразование ответа сервера
     * @param $data
     * @return array
     */
    abstract function parseResult($data): array;

    /**
     * Добавление заголовков
     */
    abstract function getFormatHeaders();

    /**
     * Преобразование данных для отправки
     * @param $data
     */
    abstract function prepareData($data);
    
}