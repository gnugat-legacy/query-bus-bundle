<?php

/*
 * This file is part of the gnugat/query-bus-bundle package.
 *
 * (c) Loïc Chardonnet <loic.chardonnet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gnugat\QueryBusBundle\Tests;

use Gnugat\QueryBusBundle\Tests\Fixtures\GetArticle;
use PHPUnit_Framework_TestCase;

class ServiceTest extends PHPUnit_Framework_TestCase
{
    const ARTICLE_ID = 42;

    private $articleRepository;
    private $queryBus;

    protected function setUp()
    {
        $kernel = new \AppKernel('test', false);
        $kernel->boot();

        $this->articleRepository = $kernel->getContainer()->get('app.article_repository');
        $this->queryBus = $kernel->getContainer()->get('gnugat_query_bus.query_bus');
    }

    /**
     * @test
     */
    public function it_finds_article_by_id()
    {
        $article = $this->queryBus->match(new GetArticle(self::ARTICLE_ID));
        $expected = $this->articleRepository->find(self::ARTICLE_ID);

        self::assertSame($expected, $article);
    }
}
