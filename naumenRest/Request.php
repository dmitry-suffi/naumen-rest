<?php

namespace suffi\naumenRest;

use suffi\naumenRest\parser\JsonParser;
use suffi\naumenRest\parser\Parser;
use suffi\naumenRest\parser\XmlParser;

/**
 * Базовый класс для запросов
 * Class Request
 * @package suffi\naumenRest
 */
class Request
{
    /**
     * Адрес апи пмс (http://host:8080/fx/api)
     * @var string
     */
    private $pmsUrl = '';

    /**
     * Дополнительный урл модуля
     * @var string
     */
    protected $url = '';

    /**
     * Пользователь
     * @var string
     */
    private $username = '';

    /**
     * Пароль
     * @var string
     */
    private $password = '';

    /**
     * Формат
     * @var string
     */
    protected $format = 'json';

    /**
     * Разрешенные форматы
     * @var array
     */
    private $allowedFormats = [
        'json',
        'xml'
    ];

    /**
     * Список ошибочных статусов
     * @var array
     */
    private $errorStatuses = [
        '204' => 'Нет данных',
        '209' => 'Конфликт данных',
        '300' => 'Ошибка! Множественный выбор',
        '400' => 'Неверный запрос или отсутствует параметр запроса',
        '401' => 'Пользователь не авторизован',
        '403' => 'У пользователя нет прав на данную операцию',
        '404' => 'Объект или один из параметров запроса не найден',
        '409' => 'Конфликт. Ресурс с данным идентификатором уже создан',
        '500' => 'Внутренняя ошибка сервера.',
        '501' => 'Запрос не доступен.',
        '503' => 'Сервис недоступен',
        '504' => 'Превышено время выполнения запроса'
    ];

    /**
     * Строка ошибки
     * @var string
     */
    private $error = '';

    /**
     * Код ошибки
     * @var string
     */
    private $errorCode = '';

    /**
     * Инстанс курла
     * @var resource
     */
    private $curl = null;

    /**
     * Парсер
     * @var Parser
     */
    private $parser = null;

    /**
     * Constructor.
     * @param $pmsUrl
     * @param $username
     * @param $password
     * @param string $format
     * @throws Exception
     */
    final public function __construct($pmsUrl, $username, $password, $format = 'json')
    {
        if (!$pmsUrl) {
            throw new Exception('Не задан адрес PMS');
        }
        if (!$username) {
            throw new Exception('Не указан пользователь');
        }
        if (!$password) {
            throw new Exception('Не указан пароль');
        }

        if (!in_array($format, $this->allowedFormats)) {
            throw new Exception('Неизвестный формат');
        }

        $this->pmsUrl = $pmsUrl;
        $this->username = $username;
        $this->password = $password;
        $this->format = $format;

        switch ($format) {
            case 'json':
            default:
                $this->parser = new JsonParser();
                break;
            case 'xml':
                $this->parser = new XmlParser();
                break;
        }

        $this->init();
    }

    /**
     * Пользовательская функция инициализации
     */
    protected function init()
    {
    }

    /**
     * @return Parser
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Код ошибки
     * @return string
     */
    final public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Текст ошибки
     * @return string
     */
    final public function getError()
    {
        return $this->error;
    }

    /**
     * Отправка GET запроса
     * @param $getParams
     * @param $parse
     * @return mixed
     */
    final protected function requestGet($getParams, $parse = true)
    {
        $this->getCurl($getParams);

        return $this->execCurl($parse ? 'parser' : '');
    }

    /**
     * Отправка POST запроса.
     * @param $postParams
     * @param string $getParams
     * @param bool $returnHeaderLocation Если true - Возвращает uiid созданного объекта
     * @param bool $prepared Флаг преобразования парсером
     * @return mixed
     * @throws Exception
     */
    final protected function requestPost($postParams, $getParams = '', $returnHeaderLocation = false, $prepared = true)
    {
        if (!$postParams) {
            throw new Exception('Не заданы данные для отправки!');
        }

        $headers = $prepared ? [] : ["Content-Type: application/x-www-form-urlencoded"];

        $this->getCurl($getParams, $prepared, $headers);

        if ($returnHeaderLocation) {
            curl_setopt($this->curl, CURLOPT_HEADER, 1);
        }
        curl_setopt($this->curl, CURLOPT_POST, true);
        $body = $prepared ? $this->parser->prepareData($postParams) : $this->prepareDefault($postParams);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);

        return $this->execCurl($returnHeaderLocation ? 'location' : 'parser');
    }

    /**
     * Отправка PUT запроса
     * @param $postParams
     * @param string $getParams
     * @return mixed
     * @throws Exception
     */
    final protected function requestPut($postParams, $getParams = '')
    {
        if (!$postParams) {
            throw new Exception('Не заданы данные для отправки!');
        }
        $this->getCurl($getParams, true, ['X-HTTP-Method-Override: PUT']);

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->parser->prepareData($postParams));

        return $this->execCurl('parser');
    }

    /**
     * Отправка DELETE запроса
     * @param $postParams
     * @param $getParams
     * @return mixed
     */
    final protected function requestDelete($postParams = [], $getParams = '')
    {
        $this->getCurl($getParams);

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $this->parser->prepareData($postParams));

        return $this->execCurl('parser');
    }

    /**
     * Инициализация инстанса курла
     * @param $getParams
     * @param bool $prepared Флаг преобразования парсером
     * @param array $headers Заголовки
     * @return void
     */
    private function getCurl($getParams, $prepared = true, array $headers = [])
    {
        $this->error = '';
        $this->errorCode = '';

        $this->curl = curl_init();

        $getParams = preg_replace('/\s+/', '%20', $getParams);

        $url = trim($this->pmsUrl, '/') . '/' . $this->format . ($getParams ?? $this->url);
        curl_setopt($this->curl, CURLOPT_URL, $url);

        if ($prepared) {
            if ($this->parser->getFormatHeaders()) {
                $headers[] = $this->parser->getFormatHeaders();
            }
        }

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }

    /**
     * Выполнение запроса
     * @param $parse
     * @return mixed
     */
    private function execCurl($parse = '')
    {
        if ($parse == 'location') {
            curl_setopt($this->curl, CURLOPT_HEADER, 1);
        }
        $result = curl_exec($this->curl);

        $info = curl_getinfo($this->curl);

        if (isset($this->errorStatuses[$info['http_code']])) {
            $this->error = $this->errorStatuses[$info['http_code']];
            $this->errorCode = $info['http_code'];
        }

        curl_close($this->curl);
        if ($this->error) {
            return false;
        }

        if ($parse == 'location') {
            //$pmsUrl = preg_quote(trim($this->pmsUrl, '/') . '/' . $this->format, '/');
            //preg_match_all('/Location: ' . $pmsUrl . '(.*?)\/([^\/\r\n]+)\r\n/', $result, $m);
            //return $m[2][0] ?? '';
            preg_match('#Location\:[^\n\r]*?\/callcases\/([0-9a-z]+)[\r\n]#usi', $result, $m);
            return $m[1] ?? '';
        } else {
            if ($parse == 'parser') {
                return $this->parser->parseResult($result);
            } else {
                return $result;
            }
        }
    }

    /**
     * @param $postParams
     * @return mixed
     */
    protected function prepareDefault($postParams)
    {
        if (!is_array($postParams)) {
            return $postParams;
        } else {
            $query = '';
            foreach ($postParams as $key => $val) {
                $query .= '&' . $key . '=' . $val;
            }
            $query = ltrim($query, '&');
            return $query;
        }
    }
}
