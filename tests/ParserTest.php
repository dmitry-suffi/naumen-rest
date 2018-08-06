<?php

namespace suffi\naumenRest\Tests;

use suffi\naumenRest\parser\JsonParser;
use suffi\naumenRest\Request;

class ParserTest extends TestCase
{

    /**
     * @param $parser
     * @return Request
     * @throws \suffi\naumenRest\Exception
     */
    public function getRequest($parser)
    {
        return new Request($this->pmsUri, $this->username, $this->password, $parser);
    }

    public function testJson()
    {
        $request = $this->getRequest('json');
        $parser = $request->getParser();

        $uiid1 = 'testCase1' . rand(1, 999);
        $uiid2 = 'testCase2' . rand(1, 999);
        $uiidParent = 'project' . rand(1, 999);
        $titleParent = 'title' . rand(1, 999);

        $this->assertEquals($parser->parseResult(''), []);
        $this->assertEquals($parser->parseResult(false), []);

        $json = '{
                    "page": null,
                    "count": 2,
                    "modifiedAfter": null,
                    "modifiedBefore": null,
                    "element":[
                        {
                            "uuid": {
                                "value": "' . $uiid1 . '"
                            },
                            "id": null,
                            "title": "testCase",
                            "parent": {
                                "uuid": {
                                    "value": "' . $uiidParent . '"
                                },
                                "title": "' . $titleParent . '"
                            }
                        },
                                                {
                            "uuid": {
                                "value": "' . $uiid2 . '"
                            },
                            "id": null,
                            "title": "testCase",
                            "parent": {
                                "uuid": {
                                    "value": "' . $uiidParent . '"
                                },
                                "title": "' . $titleParent . '"
                            }
                        }
                    ]
                }';

        $data = $parser->parseResult($json);

        $this->assertInternalType('array', $data);
        $this->assertEquals($data['count'], 2);
        $this->assertEquals(count($data['element']), 2);
        $this->assertEquals($data['element'][0]['uuid'], $uiid1);
        $this->assertEquals($data['element'][1]['uuid'], $uiid2);
        $this->assertEquals($data['element'][1]['title'], 'testCase');
        $this->assertEquals($data['element'][1]['parent']['title'], $titleParent);
        $this->assertEquals($data['element'][0]['parent']['uuid'], $uiidParent);
        $this->assertEquals($data['element'][1]['parent']['uuid'], $uiidParent);

        $this->assertEquals($parser->prepareData([
            'element' => [
                'uuid' => $uiid1,
                'title' => 'test'
            ]
        ]), '{"uuid":"' . $uiid1 . '","title":"test"}');

        $this->assertEquals($parser->prepareData([
            'elements' => [
                [
                    'uuid' => $uiid1,
                    'title' => 'test'
                ],
                [
                    'uuid' => $uiid2,
                    'title' => 'test'
                ]
            ]
        ]), '{"element":[{"uuid":"' . $uiid1 . '","title":"test"},{"uuid":"' . $uiid2 . '","title":"test"}]}');

        $this->assertEquals($parser->prepareData([
            'root' => 'elements',
            'elements' => [
                [
                    'uuid' => $uiid1,
                ],
                [
                    'uuid' => $uiid2,
                ]
            ]
        ]), '{"elements":[{"uuid":"' . $uiid1 . '"},{"uuid":"' . $uiid2 . '"}]}');

        $json = '{
            "page": null,
            "count": 1,
            "modifiedAfter": null,
            "modifiedBefore": null,
            "callcase": [
                {
                "uuid": {
                    "value": "ocpcasfs000080000lfn7ooqftmuaoa4"
                    },
                "id": null,
                "title": "testCase148",
                "parent": {
                    "uuid": {
                            "value": "corebofs000080000lej05qn9aatjtig"                        
                        },                           
                        "title": "РГС ОПС ПК ТЕСТ ДИАЛЕР"
                    },
                "priority": 1
                }
            ]
        }';

        $data = $parser->parseResult($json);

        $this->assertEquals($data['count'], 1);
        $this->assertEquals(count($data['callcase']), 1);

        $case = [
            'callcase' => [
                'title' => 'title',
                'phoneNumbers' => [
                    'phoneNumber' => [
                        [
                            'value' => '8000000000',
                            'phoneNumberType' => 'HOME',
                        ]
                    ]
                ]
            ]
        ];

        $jsonNumbers = '{"title":"title","phoneNumbers":{"phoneNumber":[{"value":"8000000000","phoneNumberType":"HOME"}]}}';

        $this->assertEquals($parser->prepareData($case), $jsonNumbers);
        $this->assertEquals($parser->parseResult($jsonNumbers), [
            'title' => 'title',
            'phoneNumbers' => [
                'phoneNumber' => [
                    [
                        'value' => '8000000000',
                        'phoneNumberType' => 'HOME',
                    ]
                ]
            ]
        ]);
    }

    public function testXml()
    {
        $request = $this->getRequest('xml');

        $parser = $request->getParser();

        $uiid1 = 'testCase1' . rand(1, 999);
        $uiid2 = 'testCase2' . rand(1, 999);
        $uiidParent = 'project' . rand(1, 999);
        $titleParent = 'title' . rand(1, 999);

        $xml = "
        <elements count=\"2\">
            <element>
                <uuid>" . $uiid1 . "</uuid>
                <title>testCase</title>
                <parent>
                    <uuid>" . $uiidParent . "</uuid>
                    <title>" . $titleParent . "</title>
                </parent>
                <creationDate>2016-10-18T09:58:24.594+03:00</creationDate>
                <completionDate>2016-10-18T09:58:24.594+03:00</completionDate>
                <lastModified>2016-10-18T09:58:24.594+03:00</lastModified>
                <state>
                    <id>new</id>
                    <title>Новое</title>
                </state>
                <priority>1</priority>
                <phoneNumbers />
                <callForm />
            </element>
            <element>
                <uuid>" . $uiid2 . "</uuid>
                <title>testCase</title>
                <parent>
                    <uuid>" . $uiidParent . "</uuid>
                    <title>" . $titleParent . "</title>
                </parent>
                <creationDate>2016-10-18T09:58:24.620+03:00</creationDate>
                <completionDate>2016-10-18T09:58:24.620+03:00</completionDate>
                <lastModified>2016-10-18T09:58:24.620+03:00</lastModified>
                <state>
                    <id>new</id>
                    <title>Новое</title>
                </state>
                <priority>1</priority>
                <phoneNumbers />
                <callForm />
            </element>
         </elements>";

        $data = $parser->parseResult($xml);

        $this->assertInternalType('array', $data);
        $this->assertEquals($data['count'], 2);

        $this->assertEquals(count($data['element']), 2);
        $this->assertEquals($data['element'][0]['uuid'], $uiid1);
        $this->assertEquals($data['element'][1]['uuid'], $uiid2);
        $this->assertEquals($data['element'][1]['title'], 'testCase');
        $this->assertEquals($data['element'][1]['parent']['title'], $titleParent);
        $this->assertEquals($data['element'][0]['parent']['uuid'], $uiidParent);
        $this->assertEquals($data['element'][1]['parent']['uuid'], $uiidParent);

        $this->assertEquals($parser->prepareData([
            'element' => [
                'uuid' => $uiid1,
                'title' => 'test'
            ]
        ]), '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<element><uuid>' . $uiid1 . '</uuid><title>test</title></element>');

        $this->assertEquals($parser->prepareData([
            'elements' => [
                [
                    'uuid' => $uiid1,
                    'title' => 'test'
                ],
                [
                    'uuid' => $uiid2,
                    'title' => 'test'
                ]
            ]
        ]), '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<elements><element><uuid>' . $uiid1 . '</uuid><title>test</title></element><element><uuid>' . $uiid2 . '</uuid><title>test</title></element></elements>');

        $this->assertEquals($parser->prepareData([
            'root' => 'elements',
            'elements' => [
                [
                    'uuid' => $uiid1,
                ],
                [
                    'uuid' => $uiid2,
                ]
            ]
        ]), '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<elements><uuid>' . $uiid1 . '</uuid><uuid>' . $uiid2 . '</uuid></elements>');

        $xml = '<callcases count="1">
                    <callcase>
                    <uuid>ocpcasfs000080000lfn78k4sbajnb9g</uuid>
                    <title>testCase1403</title>
                    <parent>
                    <uuid>corebofs000080000lej05qn9aatjtig</uuid>
                    <title>РГС ОПС ПК ТЕСТ ДИАЛЕР</title>
                     </parent>
                    <creationDate>2016-10-19T15:26:55.672+03:00</creationDate>
                    <completionDate>2016-10-19T15:26:55.680+03:00</completionDate>
                    <lastModified>2016-10-19T15:26:55.680+03:00</lastModified>
                    <state>
                    <id>adjourned</id>
                    <title>Отложенное</title>
                     </state>
                    <priority>1</priority>
                    <phoneNumbers />
                    <scheduledTime>2016-10-19T15:26:55+03:00</scheduledTime>
                    <callForm />
                     </callcase>
                </callcases>';

        $data = $parser->parseResult($xml);

        $this->assertEquals($data['count'], 1);
        $this->assertEquals(count($data['callcase']), 1);

        $case = [
            'callcase' => [
                'title' => 'title',
                'phoneNumbers' => [
                    'phoneNumber' => [
                        [
                            'value' => '8000000000',
                            'phoneNumberType' => 'HOME',
                        ]
                    ]
                ]
            ]
        ];

        $xmlNumbers = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . '<callcase><title>title</title><phoneNumbers><phoneNumber phoneNumberType="HOME">8000000000</phoneNumber></phoneNumbers></callcase>';
        $this->assertEquals($parser->prepareData($case), $xmlNumbers);
        $this->assertEquals($parser->parseResult($xmlNumbers), [
            'title' => 'title',
            'phoneNumbers' => [
                'phoneNumber' => [
                    [
                        'value' => '8000000000',
                        'phoneNumberType' => 'HOME',
                    ]
                ]
            ]
        ]);
    }
}
