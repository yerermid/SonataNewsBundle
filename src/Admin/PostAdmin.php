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

namespace Sonata\NewsBundle\Admin;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\{AbstractAdmin,AdminInterface};
use Sonata\AdminBundle\Datagrid\{DatagridMapper,ListMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType as FilterChoiceType;
use Sonata\AdminBundle\Form\Type\{ModelAutocompleteType,ModelListType};
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\FormatterBundle\Form\Type\FormatterType;
use Sonata\FormatterBundle\Formatter\Pool as FormatterPool;
use Sonata\NewsBundle\Form\Type\CommentStatusType;
use Sonata\NewsBundle\Model\CommentInterface;
use Sonata\NewsBundle\Permalink\PermalinkInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\{CheckboxType,TextareaType,ChoiceType};
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\AdminBundle\Form\Type\TemplateType;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostAdmin extends AbstractAdmin
{

    /**
     * @var FormatterPool
     */
    protected FormatterPool $formatterPool;

    /**
     * @var PermalinkInterface
     */
    protected PermalinkInterface $permalinkGenerator;

    /**
     * @var ParameterBagInterface
     */
    protected ParameterBagInterface $params;

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    public function __construct(string $code, string $class, string $baseControllerName, ParameterBagInterface $params,
                                TranslatorInterface $translator)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->params = $params;
        $this->translator = $translator;
    }

    /**
     * @param FormatterPool $formatterPool
     * @return void
     */
    public function setPoolFormatter(FormatterPool $formatterPool): void
    {
        $this->formatterPool = $formatterPool;
    }

    /**
     * @param object $post
     * @return void
     */
    public function prePersist(object $post): void
    {
        $post->setContent($this->formatterPool->transform($post->getContentFormatter(), $post->getRawContent()));
    }

    /**
     * @param object $post
     * @return void
     */
    public function preUpdate(object $post): void
    {
        $post->setContent($this->formatterPool->transform($post->getContentFormatter(), $post->getRawContent()));
    }

    /**
     * @param PermalinkInterface $permalinkGenerator
     * @return void
     */
    public function setPermalinkGenerator(PermalinkInterface $permalinkGenerator): void
    {
        $this->permalinkGenerator = $permalinkGenerator;
    }

    /**
     * @param ShowMapper $show
     * @return void
     */
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('author')
            ->add('enabled')
            ->add('title')
            ->add('abstract')
            ->add('content', null, ['safe' => true])
            ->add('tags');
    }

    /**
     * @param FormMapper $form
     * @return void
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $isHorizontal = 'horizontal' === $this->params->get('sonata.form.form_type');
        $form
            ->with('post', [
                    'class' => 'col-md-8',
                ])
                 ->add('author', ModelListType::class)
                ->add('title')
                ->add('abstract', TextareaType::class, [
                    'attr' => ['rows' => 5],
                ])
                ->add('content', FormatterType::class, [
                    'event_dispatcher' => $form->getFormBuilder()->getEventDispatcher(),
                    'format_field' => 'contentFormatter',
                    'source_field' => 'rawContent',
                    'source_field_options' => [
                        'horizontal_input_wrapper_class' => $isHorizontal ? 'col-lg-12' : '',
                        'attr' => ['class' => $isHorizontal ? 'span10 col-sm-10 col-md-10' : '', 'rows' => 20],
                    ],
                    'ckeditor_context' => 'news',
                    'target_field' => 'content',
                    'listener' => true,
                ])
            ->end()
            ->with('status', [
                    'class' => 'col-md-4',
                ])
                ->add('enabled', CheckboxType::class, ['required' => false])
                ->add('image', ModelListType::class, ['required' => false], [
                     'link_parameters' => [
                         'context' => 'news',
                         'hide_context' => true,
                     ],
                 ])

                ->add('publicationDateStart', DateTimePickerType::class, [
                    'dp_side_by_side' => true,
                ])
                ->add('commentsCloseAt', DateTimePickerType::class, [
                    'dp_side_by_side' => true,
                    'required' => false,
                ])
                ->add('commentsEnabled', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('commentsDefaultStatus', CommentStatusType::class, [
                    'expanded' => true,
                ])
            ->end()

            ->with('classification', [
                'class' => 'col-md-4',
                ])
                 ->add('tags', ModelAutocompleteType::class, [
                     'property' => 'name',
                     'multiple' => 'true',
                     'required' => false,
                 ])
                ->add('tags', null, [
                ])
                // ->add('collection', ModelListType::class, [
                 //    'required' => false,
                 //])
            ->end();
    }

    /**
     * @param ListMapper $list
     * @return void
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('custom', null, [
                'template' => '@SonataNews/Admin/list_post_custom.html.twig',
                'label' => 'list.label_post',
                'sortable' => 'title',
            ])
            ->add('commentsEnabled', null, ['editable' => true])
            ->add('publicationDateStart');
    }

    /**
     * @param DatagridMapper $filter
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title')
            ->add('enabled')
            ->add('tags', null, ['field_options' => ['expanded' => true, 'multiple' => true]])
            ->add('author')
            ->add('with_open_comments', CallbackFilter::class, [
//                'callback'   => array($this, 'getWithOpenCommentFilter'),
                'callback' => static function ($queryBuilder, $alias, $field, $data): void {
                    if (!\is_array($data) || !$data['value']) {
                        return;
                    }

                    $queryBuilder->leftJoin(sprintf('%s.comments', $alias), 'c');
                    $queryBuilder->andWhere('c.status = :status');
                    $queryBuilder->setParameter('status', CommentInterface::STATUS_MODERATE);
                },
                'field_type' => CheckboxType::class,
            ]);
    }

    /**
     * @param MenuItemInterface $menu
     * @param string $action
     * @param AdminInterface|null $childAdmin
     * @return void
     */
    protected function configureTabMenu(MenuItemInterface $menu, string $action, ?AdminInterface $childAdmin = null): void
    {
        if (!$childAdmin && !\in_array($action, ['edit'], true)) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->translator->trans('sidemenu.link_edit_post'),
            ['uri' => $admin->generateUrl('edit', ['id' => $id])]
        );

        $menu->addChild(
            $this->translator->trans('sidemenu.link_view_comments'),
            ['uri' => $admin->generateUrl('sonata.news.admin.comment.list', ['id' => $id])]
        );

        if ($this->hasSubject() && null !== $this->getSubject()->getId()) {
            $menu->addChild(
                'sidemenu.link_view_post',
                ['uri' => $admin->getRouteGenerator()->generate(
                    'sonata_news_view',
                    ['permalink' => $this->permalinkGenerator->generate($this->getSubject())]
                )]
            );
        }
    }
}
