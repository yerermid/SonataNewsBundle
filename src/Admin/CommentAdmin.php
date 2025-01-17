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

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{DatagridMapper,ListMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\Doctrine\Model\ManagerInterface;
use Sonata\NewsBundle\Form\Type\CommentStatusType;
use Sonata\NewsBundle\Model\CommentManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CommentAdmin extends AbstractAdmin
{
    /**
     * @var CommentManagerInterface
     */
    protected CommentManagerInterface $commentManager;

    // public function getBatchActions()
    // {
    //     $actions = parent::getBatchActions();

    //     $actions['enabled'] = [
    //         'label' => $this->getLabelTranslatorStrategy()->getLabel('enable', 'batch', 'comment'),
    //         'translation_domain' => $this->getTranslationDomain(),
    //         'ask_confirmation' => false,
    //     ];

    //     $actions['disabled'] = [
    //         'label' => $this->getLabelTranslatorStrategy()->getLabel('disable', 'batch', 'comment'),
    //         'translation_domain' => $this->getTranslationDomain(),
    //         'ask_confirmation' => false,
    //     ];

    //     return $actions;
    // }

    /**
     * @param object $object
     * @return void
     */
    public function postPersist(object $object): void
    {
        $this->updateCountsComment();
    }

    /**
     * @param object $object
     * @return void
     */
    public function postRemove( object $object): void
    {
        $this->updateCountsComment();
    }

    /**
     * @param object $object
     * @return void
     */
    public function postUpdate(object $object): void
    {
        $this->updateCountsComment();
    }

    /**
     * @param ManagerInterface $commentManager
     * @return void
     */
    public function setCommentManager(ManagerInterface $commentManager): void
    {
        if (!$commentManager instanceof CommentManagerInterface) {
            @trigger_error(
                'Calling the '.__METHOD__.' method with a Sonata\Doctrine\Model\ManagerInterface is deprecated'
                .' since version 2.4 and will be removed in 3.0.'
                .' Use the new signature with a Sonata\NewsBundle\Model\CommentManagerInterface instead.',
                \E_USER_DEPRECATED
            );
        }

        $this->commentManager = $commentManager;
    }

    /**
     * @param FormMapper $form
     * @return void
     */
    protected function configureFormFields(FormMapper $form): void
    {
        // define group zoning
        $form
            ->with('group_comment', ['class' => 'col-md-6'])->end()
            ->with('group_general', ['class' => 'col-md-6'])->end();

        if (!$this->isChild()) {
            $form
                ->with('group_general')
                    ->add('post', ModelListType::class)
                ->end();
        }

        $form
            ->with('group_general')
                ->add('name')
                ->add('email')
                ->add('url', null, ['required' => false])
            ->end()
            ->with('group_comment')
                ->add('status', CommentStatusType::class, [
                    'expanded' => true,
                    'multiple' => false,
                ])
                ->add('message', null, ['attr' => ['rows' => 6]])
            ->end();
    }

    /**
     * @param DatagridMapper $filter
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('email')
            ->add('message');
    }

    /**
     * @param ListMapper $list
     * @return void
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('getStatusCode', TextType::class, ['label' => 'status_code', 'sortable' => 'status']);

        if (!$this->isChild()) {
            $list->add('post');
        }

        $list
            ->add('email')
            ->add('url')
            ->add('message');
    }

    /**
     * Update the count comment.
     */
    private function updateCountsComment(): void
    {
        $this->commentManager->updateCommentsCount();
    }
}
