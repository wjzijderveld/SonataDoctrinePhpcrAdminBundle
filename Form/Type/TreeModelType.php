<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sonata\DoctrinePHPCRAdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormViewInterface;
use Symfony\Component\Form\FormView;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use Sonata\AdminBundle\Form\EventListener\MergeCollectionListener;
use Sonata\AdminBundle\Form\ChoiceList\ModelChoiceList;
use Sonata\AdminBundle\Form\DataTransformer\ModelsToArrayTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;

class TreeModelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->prependClientTransformer(new ModelToIdTransformer($options['model_manager'], $options['class']));
        $builder->setAttribute('rootNode', $options['rootNode']);
    }

    public function buildView(FormViewInterface $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->setVar('rootNode', $form->getAttribute('rootNode'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'template'          => 'doctrine_phpcr_type_tree_model',
            'model_manager'     => null,
            'class'             => null,
            'property'          => null,
            'query'             => null,
            'choices'           => null,
            'rootNode'          => '/',
            'parent'            => 'choice',
            'preferred_choices' => array(),
            'choice_list'       => function (Options $options, $previousValue) {
                return new ModelChoiceList(
                    $options['model_manager'],
                    $options['class'],
                    $options['property'],
                    $options['query'],
                    $options['choices']
                );
            }
        ));
    }

    public function getName()
    {
        return 'doctrine_phpcr_type_tree_model';
    }
}
