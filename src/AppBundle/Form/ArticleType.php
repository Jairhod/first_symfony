<?php

namespace AppBundle\Form;

use AppBundle\Entity\Tag;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('titre')
                ->add('contenu')
                ->add('date', DateType::class, array('widget' => 'single_text', 'html5' => false, 'format' => 'yyyy-MM-dd', 'attr' => ['class' => 'js-datepicker']))
                ->add('publication', null, ['required' => false, 'label' => 'Publié ?'])
                ->add('submit', SubmitType::class)
                ->add('image', ImageType::class, ['required' => false])
                ->add('tags', EntityType::class, [
                    'required' => false,
                    'class' => Tag::class,
                    'choice_label' => 'titre',
                    'expanded' => true,
                    'multiple' => true,
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('u')
                                ->orderBy('u.titre', 'ASC');
                    }
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Article'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix() {
        return 'appbundle_article';
    }

}
