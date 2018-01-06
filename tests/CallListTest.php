<?php

use suffi\naumenRest\CallList;


class CallListTest extends TestCase
{

    /**
     * @return CallList
     */
    public function getCallList()
    {
        $callList = new CallList($this->pmsUri, $this->username, $this->password);
        $callList->setProjectUuid($this->projectUiid);

        return $callList;
    }

    public function testCallList()
    {
        $callList = $this->getCallList();

        $callList->delete();

        $this->assertFalse($callList->stop());
        $this->assertTrue($callList->start());
        $this->assertTrue($callList->stop());
        $this->assertTrue($callList->update());
        $this->assertTrue($callList->start());
        $this->assertTrue($callList->delete());
    }

}