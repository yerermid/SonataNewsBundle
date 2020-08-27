<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\NewsBundle\Tests\Functional\Controller;

use PHPUnit\Framework\TestCase;
use Sonata\NewsBundle\Tests\App\AppKernel;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class TruncateControllerTest extends TestCase
{
    public function testTruncate(): void
    {
        $client = new Client(new AppKernel());
        $client->request(Request::METHOD_GET, '/u_truncate_test');

        $this->assertSame(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertSame('test', $client->getResponse()->getContent());
    }
}