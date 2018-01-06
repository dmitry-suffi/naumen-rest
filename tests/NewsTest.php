<?php

use suffi\naumenRest\News;


class NewsTest extends TestCase
{

    /**
     * @return News
     */
    public function getNews()
    {
        $news = new News($this->pmsUri, $this->username, $this->password);
        $news->setProjectUuid($this->projectUiid);

        return $news;
    }

    protected function setUp()
    {
        parent::setUp();

        $news = $this->getNews();
        $newsList = $news->getList([], true);

        if (isset($newsList['newsitem'])) {
            foreach ($newsList['newsitem'] as $newsItem) {
                $news->delete($newsItem['uuid']);
            }
        }

    }

    public function testNews()
    {
        $news = $this->getNews();

        $new = [
            'content' => 'Новость',
            'importance' => News::importanceHigh
        ];

        $id = $news->create($new);

        $getNew = $news->get($id);

        $this->assertEquals($getNew['uuid'], $id);
        $this->assertEquals($getNew['content'], $new['content']);
        $this->assertEquals($getNew['importance'], $new['importance']);

        $new2 = [
            'uuid' => $id,
            'content' => 'Новость изменена',
            'importance' => News::importanceNormal
        ];

        $news->update($new2);

        $getNew = $news->get($id);

        $this->assertEquals($getNew['uuid'], $id);
        $this->assertEquals($getNew['content'], $new2['content']);
        $this->assertEquals($getNew['importance'], $new2['importance']);

        $news->delete($id);

        $this->assertFalse($news->get($id));

    }

}