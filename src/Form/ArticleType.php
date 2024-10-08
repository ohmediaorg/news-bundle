<?php

namespace OHMedia\NewsBundle\Form;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\FileBundle\Form\Type\FileEntityType;
use OHMedia\MetaBundle\Form\Type\MetaEntityType;
use OHMedia\TimezoneBundle\Form\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $article = $options['data'];

        $builder->add('title');

        $builder->add('slug', null, [
            'required' => false,
            'help' => 'Leave blank to auto-generate',
            'empty_data' => '',
        ]);

        $builder->add('author', null, [
            'required' => false,
        ]);

        $builder->add('snippet', TextareaType::class, [
            'required' => false,
            'attr' => [
                'rows' => 5,
            ],
        ]);

        $builder->add('content', WysiwygType::class);

        $builder->add('image', FileEntityType::class, [
           'image' => true,
           'data' => $article->getImage(),
        ]);

        $builder->add('tags', EntityType::class, [
            'class' => ArticleTag::class,
            'multiple' => true,
            'expanded' => true,
        ]);

        $builder->add('meta', MetaEntityType::class, [
            'data' => $article->getMeta(),
        ]);

        $builder->add('publish_datetime', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
                'help' => 'If empty or in the future, this project will not be visible on the frontend.',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
