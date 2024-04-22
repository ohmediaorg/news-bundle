<?php

namespace OHMedia\NewsBundle\Form;

use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Entity\ArticleTag;
// use Doctrine\ORM\QueryBuilder;
use OHMedia\FileBundle\Form\Type\FileEntityType;
use OHMedia\MetaBundle\Form\Type\MetaEntityType;
use OHMedia\TimezoneBundle\Form\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
// use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
// use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// use Symfony\Component\Form\Extension\Core\Type\EmailType;
// use Symfony\Component\Form\Extension\Core\Type\IntegerType;
// use Symfony\Component\Form\Extension\Core\Type\MoneyType;
// use Symfony\Component\Form\Extension\Core\Type\NumberType;
// use Symfony\Component\Form\Extension\Core\Type\TelType;
use OHMedia\WysiwygBundle\Form\Type\WysiwygType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// use Symfony\Component\Form\Extension\Core\Type\TextType;
// use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $article = $options['data'];

        $builder->add('title');

        //TODO show this?
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
        // TODO label
        $builder->add('ArticleTag', EntityType::class, [
            'class' => ArticleTag::class,
            // 'label' => 'Tags',
            // 'choice_label' => 'tag',
            'multiple' => true,
            'expanded' => true,
        ]);

        $builder->add('meta', MetaEntityType::class, [
            'data' => $article->getMeta(),
        ]);

        $builder->add('publish_datetime', DateTimeType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
