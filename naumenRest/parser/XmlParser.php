<?php

namespace suffi\naumenRest\parser;

/**
 * Обработка XML
 * Class XmlParser
 * @package suffi\naumenRest\parser
 */
class XmlParser extends Parser
{
    /**
     * {@inheritdoc}
     */
    public function parseResult($data): array
    {
        if (!$data) {
            return [];
        }

        $data = preg_replace('/<([^ ]+) ([^>]*)>([^<>]*)<\/\\1>/i', '<$1 $2><value>$3</value></$1>', $data);

        $xml = simplexml_load_string($data, "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($xml);
        $result = json_decode($json, true);

        $result = $this->parseAttributes($result);

        return $result;
    }


    /**
     * {@inheritdoc}
     */
    public function getFormatHeaders()
    {
        return "Content-Type: application/xml";
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($data)
    {
        return $data ? trim($this->xmlEncode($data)) : '';
    }

    private function xmlEncode($array, $node = null, $headNode = '', $attribute = false)
    {
        $root = '';
        if (is_null($node)) {
            $root = $array['root'] ?? '';
            unset($array['root']);

            if (count($array) == 1) {
                $headNode = key($array);
                $array = current($array);
            } else {
                $headNode = $root ?? 'root';
            }
            $node = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><' . $headNode . '></' . $headNode . '>', null);
        }
        if (is_array($array) && count($array) == 1 && isset($array[0])) {
            $array = $array[0];
        }

        if (!$array) {
            return '';
        }

        if (substr($headNode, -1, 1) == 's') {
            $headNode = substr($headNode, 0, -1);
        }

        $attr = false;
        if (is_array($array) && count($array) == 1) {
            if (isset($array[$headNode]) && is_array($array[$headNode]) && isset($array[$headNode][0])) {
                $attr = true;
            }
        }

        foreach ($array as $key => $value) {
            if (is_numeric($key)) {
                $key = $headNode;
                if ($root) {
                    if (is_array($value) && count($value) == 1) {
                        $key = key($value);
                        $value = current($value);
                    }
                }
            }
            if (is_array($value)) {
                $subnode = $node->addChild($key);
                $this->xmlEncode($value, $subnode, $key, $attr);
            } else {
                if ($attribute && $key != 'value') {
                    $node->addAttribute($key, $value);
                } else {
                    if ($attribute && $key == 'value') {
                        dom_import_simplexml($node)->nodeValue = $value;
                    } else {
                        $node->addChild($key, $value);
                    }
                }
            }
        }
        return $node->asXML();
    }

    /**
     * @param $result
     * @param string $keyNode
     * @return array
     */
    protected function parseAttributes($result, $keyNode = '')
    {
        $toArray = false;
        if (isset($result['@attributes']) && count($result) == 2) {
            $keys = array_diff(array_keys($result), ['@attributes']);
            $key = array_shift($keys);

            if (is_array($result[$key]) && !isset($result[$key][0])) {
                $result[$key] = [0 => $result[$key]];
            }
            if ($key == 'value' && !is_array($result[$key])) {
                $toArray = true;
            }
        }

        if (isset($result['@attributes'])) {
            foreach ($result['@attributes'] as $key => $value) {
                $result[$key] = $value;
            }
            unset($result['@attributes']);
        }

        if ($toArray && !is_numeric($keyNode)) {
            $result = [$result];
        } else {
            foreach ($result as $key => $item) {
                if (is_array($item)) {
                    $result[$key] = $this->parseAttributes($item, $key);
                }
            }
        }
        return $result;
    }
}
