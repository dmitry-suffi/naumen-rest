<?php

namespace suffi\naumenRest\Tests;

use suffi\naumenRest\CallCases;

class CallCasesTest extends TestCase
{
    protected $parser = 'json';

    /**
     * @return CallCases
     */
    public function getCallCases()
    {
        $callCases = new CallCases($this->pmsUri, $this->username, $this->password, $this->parser);
        $callCases->setProjectUuid($this->projectUiid);

        return $callCases;
    }

    protected function setUp()
    {
        parent::setUp();

        $callCases = $this->getCallCases();
        $cases = $callCases->getList([], true);

        if (isset($cases['callcase'])) {
            $callCases->deleteList($cases['callcase']);
        }
    }

    public function testCreateEmptyCase()
    {
        $callCases = $this->getCallCases();

        $this->assertFalse($callCases->create([]));
    }

    public function testCreateCase()
    {
        $callCases = $this->getCallCases();

        $create = $callCases->create([
            'title' => 'testCase'
        ]);

        $this->assertNotFalse($create);
        $this->assertEquals($callCases->getErrorCode(), '');

        $title = 'testCase' . rand(0, 999);
        $newUiid = $callCases->create([
            'title' => $title
        ]);

        $case = $callCases->get($newUiid);

        $this->assertEquals($case['title'], $title);
        $this->assertEquals($case['uuid'], $newUiid);
        $this->assertEquals($case['parent']['uuid'], $callCases->getProjectUuid());
    }

    public function testDeleteCase()
    {
        $callCases = $this->getCallCases();

        $title = 'testCase' . rand(0, 999);
        $newUiid = $callCases->create([
            'title' => $title
        ]);

        $case = $callCases->get($newUiid);

        $this->assertEquals($case['title'], $title);
        $this->assertEquals($case['uuid'], $newUiid);

        $this->assertTrue($callCases->delete($case['uuid']));

        $this->assertFalse($callCases->get($newUiid));
    }

    public function testGetList()
    {
        $callCases = $this->getCallCases();
        $cases = $callCases->getList();

        $this->assertFalse($cases);
        $this->assertEquals($callCases->getErrorCode(), '204');

        $title1 = 'testCase1' . rand(0, 999);
        $title2 = 'testCase2' . rand(0, 999);
        $callCases->create([
            'title' => $title1
        ]);
        $callCases->create([
            'title' => $title2
        ]);

        $cases = $callCases->getList();
        $this->assertInternalType('array', $cases);
        $this->assertEquals($cases['count'], '2');
        $cond1 = $cases['callcase'][0]['title'] == $title1 && $cases['callcase'][1]['title'] == $title2;
        $cond2 = $cases['callcase'][0]['title'] == $title2 && $cases['callcase'][1]['title'] == $title1;
        $this->assertTrue($cond1 || $cond2);

        $this->assertTrue($callCases->deleteList($cases['callcase']));

        $cases = $callCases->getList();
        $this->assertFalse($cases);
        $this->assertEquals($callCases->getErrorCode(), '204');

        $title1 = 'testCase1' . rand(0, 999);
        $title2 = 'testCase2' . rand(0, 999);
        $newUiid1 = $callCases->create([
            'title' => $title1
        ]);
        $callCases->create([
            'title' => $title2
        ]);

        $callCases->setState($newUiid1, ['state' => 'adjourned']);

        $cases = $callCases->getList(['state' => 'adjourned']);

        $this->assertInternalType('array', $cases);
        $this->assertEquals($cases['count'], '1');

        $this->assertEquals($cases['callcase'][0]['title'], $title1);
        $this->assertEquals($cases['callcase'][0]['uuid'], $newUiid1);
        $this->assertEquals($cases['callcase'][0]['state']['id'], 'adjourned');
    }

    public function testGetFullList()
    {
        $callCases = $this->getCallCases();
        $cases = $callCases->getList();

        $this->assertFalse($cases);
        $this->assertEquals($callCases->getErrorCode(), '204');

        $cases = [];
        $count = 305;
        for ($i = 0; $i < $count; $i++) {
            $title = 'testCase' . $i . rand(0, 999);
            $cases[] = [
                'title' => $title
            ];
        }

        $callCases->createList($cases);

        $cases = $callCases->getList([], true);

        $this->assertInternalType('array', $cases);
        $this->assertEquals($cases['count'], $count);
    }

    public function testStateCase()
    {
        $callCases = $this->getCallCases();

        $title1 = 'testCase1' . rand(0, 999);
        $newUiid1 = $callCases->create([
            'title' => $title1,
            'phoneNumbers' => [
                'phoneNumber' => [
                    [
                        'value' => '8000000000',
                        'phoneNumberType' => 'HOME',
                    ]
                ]
            ]
        ]);

        $res = $callCases->setState($newUiid1, ['state' => 'adjourned']);

        $this->assertTrue($res);

        $case = $callCases->get($newUiid1);

        $this->assertEquals($case['uuid'], $newUiid1);
        $this->assertEquals($case['state']['id'], 'adjourned');

        $state = $callCases->getState($newUiid1);

        $this->assertEquals($state, 'adjourned');
    }

    public function testUpdate()
    {
        $callCases = $this->getCallCases();

        $title = 'testCase1' . rand(0, 999);
        $create = $callCases->create([
            'title' => $title
        ]);

        $this->assertNotFalse($create);
        $this->assertEquals($callCases->getErrorCode(), '');

        $case = $callCases->get($create);

        $this->assertEquals($case['title'], $title);
        $this->assertEquals($case['uuid'], $create);
        $this->assertEquals($case['parent']['uuid'], $callCases->getProjectUuid());

        $titleNew = 'testCase2' . rand(0, 999);
        $update = $callCases->update(
            [
                'uuid' => $create,
                'title' => $titleNew
            ]
        );

        $this->assertNotFalse($update);
        $this->assertEquals($callCases->getErrorCode(), '');

        $case = $callCases->get($create);

        $this->assertNotEquals($case['title'], $title);
        $this->assertEquals($case['title'], $titleNew);
        $this->assertEquals($case['uuid'], $create);
        $this->assertEquals($case['parent']['uuid'], $callCases->getProjectUuid());
    }

    public function testExtIdCase()
    {
        $callCases = $this->getCallCases();

        $id = 'my case ' . rand(0, 999);
        $title = 'testCase1' . rand(0, 999);
        $create = $callCases->createWithId([
            'id' => $id,
            'title' => $title
        ]);

        $this->assertNotFalse($create);
        $this->assertEquals($callCases->getErrorCode(), '');

        $case = $callCases->getByExtId($id);

        $this->assertEquals($case['title'], $title);
        $this->assertEquals($case['id'], $id);
        $this->assertEquals($case['uuid'], $create);
        $this->assertEquals($case['parent']['uuid'], $callCases->getProjectUuid());

        $titleNew = 'testCase2' . rand(0, 999);
        $update = $callCases->updateByExtId(
            [
                'id' => $id,
                'title' => $titleNew
            ]
        );

        $this->assertNotFalse($update);
        $this->assertEquals($callCases->getErrorCode(), '');

        $case = $callCases->getByExtId($id);

        $this->assertNotEquals($case['title'], $title);
        $this->assertEquals($case['title'], $titleNew);
        $this->assertEquals($case['id'], $id);
        $this->assertEquals($case['uuid'], $create);
        $this->assertEquals($case['parent']['uuid'], $callCases->getProjectUuid());

        $del = $callCases->deleteByExtId($id);
        $this->assertTrue($del);

        $case = $callCases->getByExtId($id);
        $this->assertFalse($case);
    }

    public function testList()
    {
        $callCases = $this->getCallCases();
        $cases = $callCases->getList();

        $this->assertFalse($cases);
        $this->assertEquals($callCases->getErrorCode(), '204');

        $cases = [];
        $count = 40;
        for ($i = 0; $i < $count; $i++) {
            $title = 'testCase' . $i . rand(0, 999);
            $cases[] = [
                'id' => 'id' . $i,
                'title' => $title,
                'comment' => 'comment1',
                'phoneNumbers' => [
                    'phoneNumber' => [
                        [
                            'value' => '8000000000',
                            'phoneNumberType' => 'HOME',
                        ]
                    ]
                ]
            ];
        }

        $result = $callCases->createList($cases);

        $this->assertEquals(count($result['result']), $count);

        foreach ($result['result'] as $item) {
            $this->assertEquals($item['code'], 'SUCCESS');
        }

        foreach ($cases as $key => $case) {
            $cases[$key]['comment'] = 'commentNew';
        }

        $result = $callCases->updateList($cases);

        $this->assertEquals(count($result['result']), $count);
        foreach ($result['result'] as $item) {
            $this->assertEquals($item['code'], 'SUCCESS');
        };

        $cases = $callCases->getList();

        foreach ($cases['callcase'] as $case) {
            $this->assertEquals($case['comment'], 'commentNew');
        }
    }
}
