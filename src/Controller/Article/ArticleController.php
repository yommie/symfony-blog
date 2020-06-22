<?php

namespace App\Controller\Article;

use App\Entity\Article;
use App\Form\CreateArticleType;
use App\Service\ArticleService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    /**
     * @Route("/app/articles/create", name="createArticle")
     */
    public function createArticle(
        Request $request,
        ArticleService $articleService,
        KernelInterface $kernel
    ): Response {
        $articleForm = $this->createForm(CreateArticleType::class);

        $articleForm->handleRequest($request);

        if($articleForm->isSubmitted()) {
            if($articleForm->isValid()) {
                $data = $articleForm->getData();

                try {
                    $article = $articleService->createArticle(
                        $this->getUser(),
                        $data["title"],
                        $data["content"],
                        $data["featureImage"],
                        $kernel->getProjectDir(),
                        $data["isCommentsAllowed"],
                        $data["shouldPublish"]
                    );

                    return $this->redirectToRoute("viewArticle", ["slug" => $article->getSlug()]);
                } catch(\Exception $e) {
                    $this->addFlash("create-article-error", $e->getMessage());
                }
            }
        } else {
            $articleForm->get("isCommentsAllowed")->setData(true);
            $articleForm->get("shouldPublish")->setData(true);
        }

        return $this->render('articles/create.html.twig', [
            'createForm' => $articleForm->createView(),
        ]);
    }





    /**
     * @Route("/article/{slug}", name="viewArticle")
     */
    public function viewArticle(
        string $slug
    ): Response {
        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy([
            "slug" => $slug
        ]);

        if(null === $article) {
            // TODO: Return a 404 error response
            $this->addFlash("home-error", "Article not found");
            return $this->redirectToRoute("home");
        }

        $content = "";

        try {
            $content = (new \DBlackborough\Quill\Render($article->getContent()))->render();
        } catch(\Exception $e) {
            $this->addFlash("view-error", $e->getMessage());
        }

        return $this->render("articles/view.html.twig", [
            "article" => $article,
            "content" => $content
        ]);
    }
}
