<?php

namespace suffi\naumenRest;

/**
 * Запросник для новостей
 * Class News
 * @package suffi\naumenRest
 */
class News extends Request
{
    /**
     * Обычная
     */
    const IMPORTANCE_NORMAL = 'NORMAL';

    /**
     * Важная
     */
    const IMPORTANCE_HIGH = 'HIGH';

    /**
     * Дополнительный урл модуля
     * @var string
     */
    protected $url = '/news/';

    /**
     * Uiid проекта
     * @var string
     */
    private $projectUuid = '';

    /**
     * @param string $projectUuid
     */
    public function setProjectUuid($projectUuid)
    {
        $this->projectUuid = $projectUuid;
    }

    /**
     * @return string
     */
    public function getProjectUuid()
    {
        return $this->projectUuid;
    }

    /**
     * Получение новости
     * @param string $id Uiid кейса
     * @return mixed
     */
    public function get($id)
    {
        return $this->requestGet('/news/' . $id);
    }

    /**
     * Создание новости
     * @param $data
     * @return string|false
     * @throws Exception
     */
    public function create($data)
    {
        $data['owner'] = $data['owner'] ?? $this->projectUuid;
        $uiid = $this->requestPost(['news' => $data], '/news/?owner=' . $this->projectUuid, true);
        if ($this->getErrorCode()) {
            return false;
        } else {
            return $uiid;
        }
    }

    /**
     * Обновление кейса
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function update($data)
    {
        $this->requestPut(['news' => $data], '/news/');
        if ($this->getErrorCode()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Удаление кейса
     * @param string $id Uiid кейса
     * @return mixed
     */
    public function delete($id)
    {
        $this->requestDelete([], '/news/' . $id);
        if ($this->getErrorCode()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Получение списка
     * @param array $params Дополнительные параметры
     *      owner - Проект
     *      page - Cтраница
     * @param bool $full Флаг полной выборки. Если включен, делает запросы всех страниц списка
     * @return array|false
     */
    public function getList(array $params = [], $full = false)
    {
        $getParams = '/news/?owner=' . $this->projectUuid;
        if ($params) {
            foreach ($params as $key => $value) {
                if ($full && $key == 'page') {
                    continue;
                }
                $getParams .= '&' . $key . '=' . $value;
            }
        }

        if ($full) {
            $data = [];
            $i = 1;
            do {
                $pageData = $this->requestGet($getParams . '&page=' . $i);
                $i++;
                if (isset($pageData['newsitem'])) {
                    foreach ($pageData['newsitem'] as $value) {
                        $data['newsitem'][] = $value;
                    }
                }
            } while (count($pageData['newsitem']) > 0 && !$this->getError());
        } else {
            $data = $this->requestGet($getParams);
        }

        if (!isset($data['count']) && isset($data['newsitem'])) {
            $data['count'] = count($data['newsitem']);
        }

        if (isset($data['count']) && $data['count'] == 1 && isset($data['newsitem']) && !isset($data['newsitem'][0])) {
            $data['newsitem'] = [0 => $data['newsitem']];
        }

        return $data;
    }
}
