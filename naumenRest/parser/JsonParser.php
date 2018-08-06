<?php

namespace suffi\naumenRest\parser;

/**
 * Обработка JSON
 * Class JsonParser
 * @package suffi\naumenRest\parser
 */
class JsonParser extends Parser
{
    /**
     * {@inheritdoc}
     */
    public function parseResult($data): array
    {
        if (!$data) {
            return [];
        }
        $data = json_decode($data, true);
        if (!$data) {
            return [];
        }
        $this->changeArray($data);
        return $data;
    }

    private function changeArray(array &$array)
    {
        foreach ($array as $key => &$value) {
            if (is_array($value)) {
                if (count($value) == 1 && isset($value['value'])) {
                    $value = $value['value'];
                } else {
                    $this->changeArray($value);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatHeaders()
    {
        return "Content-Type: application/json";
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($data)
    {
        $root = $data['root'] ?? '';
        unset($data['root']);

        if (is_array($data) && count($data) == 1) {
            reset($data);
            $values = current($data);
            if (is_array($values) && isset($values[0])) {
                if ($root) {
                    $key = $root;
                } else {
                    $key = key($data);
                    if (substr($key, -1, 1) == 's') {
                        $key = substr($key, 0, -1);
                    }
                }
                $data = [$key => $values];
            } else {
                $data = $values;
            }
        }

        if (!$data) {
            return '';
        }

        return json_encode($data);
    }
}
