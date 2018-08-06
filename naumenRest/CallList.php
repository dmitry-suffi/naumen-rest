<?php

namespace suffi\naumenRest;

/**
 * Запросник для управления состоянием листа обзвона
 * Class CallList
 * @package suffi\naumenRest
 */
class CallList extends Request
{

    /**
     * Дополнительный урл модуля
     * @var string
     */
    protected $url = '/projects/';

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
     * @param $state
     * @return bool
     * @throws Exception
     */
    protected function setState($state)
    {
        $this->requestPost(['cmd' => $state], '/projects/' . $this->projectUuid . '/calllist/', false, false);
        if ($this->getErrorCode()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Запуск
     * @return bool
     */
    public function start()
    {
        return $this->setState('start');
    }

    /**
     * Приостановка
     * @return bool
     */
    public function stop()
    {
        return $this->setState('stop');
    }

    /**
     * Обновление колллиста
     * @return bool
     */
    public function update()
    {
        return $this->setState('update');
    }

    /**
     * Удаление колллиста
     * @return bool
     */
    public function delete()
    {
        return $this->setState('delete');
    }
}
