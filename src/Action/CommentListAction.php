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

namespace Sonata\NewsBundle\Action;

use Sonata\NewsBundle\Model\{CommentInterface,CommentManagerInterface};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class CommentListAction extends AbstractController
{
    /**
     * @var CommentManagerInterface
     */
    private CommentManagerInterface $commentManager;

    /**
     * @param CommentManagerInterface $commentManager
     */
    public function __construct(CommentManagerInterface $commentManager)
    {
        $this->commentManager = $commentManager;
    }

    /**
     * @param int $postId
     *
     * @return Response
     */
    public function __invoke(int $postId): Response
    {
        $pager = $this->commentManager
            ->getPaginator([
                'postId' => $postId,
                'status' => CommentInterface::STATUS_VALID,
            ], 1, 500); //no limit

        return $this->render('@SonataNews/Post/comments.html.twig', [
            'pager' => $pager,
        ]);
    }
}
