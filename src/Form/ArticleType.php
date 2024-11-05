<?php

namespace OHMedia\NewsBundle\Form;

use OHMedia\FileBundle\Form\Type\FileEntityType;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
use OHMedia\TimezoneBundle\Form\Type\DateTimeType;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function __construct(
        #[Autowire('%oh_media_news.article_tags%')]
        private bool $enabledArticleTags,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $article = $options['data'];

        $builder->add('title');

        $builder->add('author', null, [
            'required' => false,
        ]);

        $builder->add('snippet', TextareaType::class, [
            'attr' => [
                'rows' => 5,
            ],
        ]);

        $builder->add('content', WysiwygType::class);

        $builder->add('image', FileEntityType::class, [
            'image' => true,
            'data' => $article->getImage(),
        ]);

        if ($this->enabledArticleTags) {
            $builder->add('tags', EntityType::class, [
                'required' => false,
                'class' => ArticleTag::class,
                'multiple' => true,
                'expanded' => true,
            ]);
        }

        $builder->add('publish_at', DateTimeType::class, [
            'required' => false,
            'widget' => 'single_text',
            'help' => 'If empty or in the future, this article will not be visible on the frontend.',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
