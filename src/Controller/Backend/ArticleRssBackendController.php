<?php

namespace OHMedia\NewsBundle\Controller\Backend;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class ArticleRssBackendController extends AbstractController
{
    #[Route('/articles/rss', name: 'article_rss_settings', methods: ['GET'])]
    public function settings(): Response
    {
        // TODO You can use the Settings service from the settings-bundle to save these values

        // TODO voter

        $fb = $this->createFormBuilder();

        $fb->add('title', TextType::class, [
            'label' => 'RSS Title',
            'data' => 'TODO',
        ]);

        $fb->add('desc', TextType::class, [
            'label' => 'RSS Description',
            'data' => 'TODO',
        ]);

        $form = $fb->getForm();

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            // TODO save the settings values
            // $settings->set(SETTING_ID_HERE, $formData[FORM_FIELD_NAME_HERE]);

            $this->addFlash('notice', 'RSS settings updated successfully');

            return $this->redirectToRoute('article_rss');
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
