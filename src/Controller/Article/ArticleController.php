<?php

namespace App\Controller\Article;

use App\Entity\Article;
use App\Form\CreateArticleType;
use App\Service\ArticleService;
use App\Form\CreateArticleCommentType;
use App\Repository\ArticleCommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $this->denyAccessUnlessGranted("CREATE_POST", new Article());

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
     * @Route("/article/{slug}/{page}", name="viewArticle", requirements={"page"="\d+"})
     */
    public function viewArticle(
        string $slug,
        int $page = 1,
        ArticleCommentRepository $articleCommentRepository
    ): Response {
        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy([
            "slug" => $slug
        ]);

        if(null === $article) {
            // TODO: Return a 404 error response
            $this->addFlash("home-error", "Article not found");
            return $this->redirectToRoute("home");
        }

        if(
            $article->getAuthor() !== $this->getUser() &&
            (
                $article->getIsBanned() === true ||
                $article->getIsPublished() === false
            )
        ) {
            $this->addFlash("home-error", "Article not found");
            return $this->redirectToRoute("home");
        }

        $content = "";

        try {
            $content = (new \DBlackborough\Quill\Render($article->getContent()))->render();
        } catch(\Exception $e) {
            $this->addFlash("view-error", $e->getMessage());
        }

        $formattedComments = [];
        $recordsPerPage = $this->getParameter("records_per_page");
        $comments = $articleCommentRepository->fetchArticleComments($recordsPerPage, $page, $article);
        $pages = ceil($comments["totalMatched"] / $recordsPerPage);
        foreach($comments["paginator"] as $comment) {
            try {
                $formattedComments[] = [
                    "author" => $comment->getUser()->getUsername(),
                    "comment" => (new \DBlackborough\Quill\Render($comment->getComment()))->render(),
                    "createdDate" => $comment->getCreatedDate()->format("jS F, Y h:i:s")
                ];
            } catch(\Exception $e) {}
        }

        return $this->render("articles/view.html.twig", [
            "article" => $article,
            "content" => $content,
            "commentsCount" => $comments["totalMatched"],
            "comments" => $formattedComments,
            "page" => $page,
            "pages" => $pages
        ]);
    }





    /**
     * @Route("/app/article/{slug}/comments/create", name="createArticleComment")
     */
    public function createArticleComment(
        Request $request,
        string $slug,
        ArticleService $articleService
    ): Response {
        $article = $this->getDoctrine()->getRepository(Article::class)->findOneBy([
            "slug" => $slug
        ]);

        if(null === $article) {
            // TODO: Return a 404 error response
            $this->addFlash("home-error", "Article not found");
            return $this->redirectToRoute("home");
        }

        $this->denyAccessUnlessGranted("COMMENT_ON_POST", $article);

        $commentForm = $this->createForm(CreateArticleCommentType::class);
        $commentForm->handleRequest($request);

        if($commentForm->isSubmitted() && $commentForm->isValid()) {
            $data = $commentForm->getData();

            try {
                $articleService->createArticleComment($article, $this->getUser(), $data["content"]);

                return $this->redirectToRoute("viewArticle", ["slug" => $article->getSlug()]);
            } catch(\Exception $e) {
                $this->addFlash("create-comment-error", $e->getMessage());
            }
        }

        return $this->render("articles/create-comment.html.twig", [
            "article" => $article,
            "createForm" => $commentForm->createView()
        ]);
    }
}
