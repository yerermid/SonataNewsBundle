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

namespace Sonata\NewsBundle\Block\Breadcrumb;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\NewsBundle\Model\BlogInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * NEXT MAJOR: Replace EngineInterface dependency
 * BlockService for post breadcrumb.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
final class NewsPostBreadcrumbBlockService extends BaseNewsBreadcrumbBlockService
{
    /**
     * @var BlogInterface
     */
    protected BlogInterface $blog;

    /**
     * @param string $context
     * @param string $name
     */
    public function __construct($context, $name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, BlogInterface $blog)
    {
        $this->blog = $blog;

        parent::__construct($context, $name, $templating, $menuProvider, $factory);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'sonata.news.block.breadcrumb_post';
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureSettings(OptionsResolver $resolver): void
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults([
            'post' => false,
        ]);
    }

    /**
     * @param BlockContextInterface $blockContext
     * @return ItemInterface
     */
    protected function getMenu(BlockContextInterface $blockContext): ItemInterface
    {
        $menu = $this->getRootMenu($blockContext);

        if ($post = $blockContext->getBlock()->getSetting('post')) {
            $menu->addChild($post->getTitle(), [
                'route' => 'sonata_news_view',
                'routeParameters' => [
                    'permalink' => $this->blog->getPermalinkGenerator()->generate($post),
                ],
                'extras' => [
                    'translation_domain' => false,
                ],
            ]);
        }

        return $menu;
    }
}
