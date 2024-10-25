<?php

namespace OHMedia\NewsBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\NewsBundle\Entity\Article;
use OHMedia\NewsBundle\Security\Voter\ArticleVoter;
use OHMedia\SettingsBundle\Service\Settings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class ArticleRssBackendController extends AbstractController
{
    #[Route('/articles/rss', name: 'article_rss_settings')]
    public function settings(
        Request $request,
        Settings $settings
    ): Response {
        $newArticle = new Article();

        $this->denyAccessUnlessGranted(
            ArticleVoter::SETTINGS,
            $newArticle,
            'You cannot access the article settings.'
        );

        $fb = $this->createFormBuilder();

        $fb->add(Article::SETTING_RSS_TITLE, TextType::class, [
            'label' => 'RSS Feed Title',
            'data' => $settings->get(Article::SETTING_RSS_TITLE),
        ]);

        $fb->add(Article::SETTING_RSS_DESC, TextType::class, [
            'label' => 'RSS Feed Description',
            'data' => $settings->get(Article::SETTING_RSS_DESC),
        ]);

        $fb->add('save', SubmitType::class);

        $form = $fb->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $settings->set(Article::SETTING_RSS_TITLE, $form->get(Article::SETTING_RSS_TITLE)->getData());
                $settings->set(Article::SETTING_RSS_DESC, $form->get(Article::SETTING_RSS_DESC)->getData());

                $this->addFlash('notice', 'RSS settings updated successfully');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaNews/backend/rss.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
