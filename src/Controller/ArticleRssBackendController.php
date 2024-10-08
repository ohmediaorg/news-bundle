<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OHMedia\NewsBundle\Entity\Article;

#[Admin]
class ArticleRssBackendController extends AbstractController
{
    #[Route('/articles/rss', name: 'article_rss_settings')]
    public function settings(
        Request $request,
        Settings $settings
    ): Response {
        $rssSettings = Article::getRssSettings();
        $fb = $this->createFormBuilder();

        foreach ($rssSettings as $id => $label) {
            $fb->add($id, TextType::class, [
                'label' => $label,
                'data' => $settings->get($id),
            ]);
        }

        $fb->add('save', SubmitType::class);

        $form = $fb->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            foreach ($rssSettings as $id => $label) {
                $entity = $formData[$id];

                $settings->set($id, $entity);
            }

            $this->addFlash('notice', 'RSS settings updated successfully');
        }

        return $this->render('@OHMediaNews/backend/article_tag/article_tag_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
