<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ArticleService {

    private const ALLOWED_FEATURE_IMAGE_MIMES = ['image/jpeg', 'image/jpg', 'image/png'];
    private const MAX_FEATURE_IMAGE_FILE_SIZE = 5242880;

    /**
     * Entity Manager
     * 
     * @var EntityManagerInterface Entity Manager
     */
    private $entityManager;

    /**
     * Authorization Checker
     * 
     * @var AuthorizationCheckerInterface Entity Manager
     */
    private $authorizationChecker;





    /**
     * Service Constructor
     * 
     * @param EntityManagerInterface Entity Manager
     * @param AuthorizationCheckerInterface Authorization Checker
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
       $this->entityManager = $entityManager;
       $this->authorizationChecker = $authorizationChecker;
    }





    /**
     * Create Article
     * 
     * @param User Author
     * @param string Title
     * @param string Content
     * @param UploadedFile Feature Image
     * @param string Project Directory
     * @param bool Is Comments Allowed
     * @param bool Should Publish Article
     * 
     * @throws FileException
     * @throws \Exception
     * 
     * @return Article Created Article
     */
    public function createArticle(
        User $user,
        string $title,
        string $content,
        UploadedFile $featureImage,
        string $projectDirectory,
        bool $isCommentsAllowed,
        bool $shouldPublish = false
    ): Article {
        $article = new Article();

        if(!$this->authorizationChecker->isGranted("CREATE_POST", $article)) {
            throw new \Exception("Sorry, you do not have the permission to create articles");
        }

        // TODO: Check for Banned Words

        if($featureImage->getSize() > self::MAX_FEATURE_IMAGE_FILE_SIZE) {
            throw new FileException(sprintf(
                "File size '%s' for feature image too large. Max file size allowed is '%s'",
                \App\Util\File::formatBytes($featureImage->getSize()),
                \App\Util\File::formatBytes(self::MAX_FEATURE_IMAGE_FILE_SIZE)
            ));
        }

        if(!in_array($featureImage->getMimeType(), self::ALLOWED_FEATURE_IMAGE_MIMES, true)) {
            throw new FileException(sprintf(
                "'%s' file type not allowed. Allowed file types are: %s",
                $featureImage->getMimeType(),
                implode(", ", self::ALLOWED_FEATURE_IMAGE_MIMES)
            ));
        }

        $slug = $this->generateArticleSlug($title);
        $excerpt = $this->generateArticleExcerpt($content);

        $directory = $projectDirectory . '/public/storage/articles/' . $slug . "/";
        $fileName = \App\Util\File::generateFileName($directory, $featureImage->guessExtension());

        $featureImage->move($directory, $fileName);

        $featureImagePath = '/storage/articles/' . $slug . '/' . $fileName;

        $article->setAuthor($user);
        $article->setTitle($title);
        $article->setExcerpt($excerpt);
        $article->setFeatureImage($featureImagePath);
        $article->setContent($content);
        $article->setIsBanned(false);
        $article->setIsCommentsAllowed($isCommentsAllowed);
        $article->setIsPublished($shouldPublish);
        $article->setSlug($slug);
        $article->setCreatedDate(new \DateTime());

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // TODO: Notify Subcribed members

        return $article;
    }





    /**
     * Generate Article Slug
     * 
     * @param string Article Title
     * 
     * @throws \Exception
     * 
     * @return string Generated Article Slug
     */
    private function generateArticleSlug(string $title): string {
        $slug = $originalSlug = \App\Util\Strings::getSlug($title);

        if(null === $slug) {
            throw new \Exception(sprintf("Unable to generate slug for title '%s'", $title));
        }

        $counter = 1;

        while(null !== $this->entityManager->getRepository(Article::class)->findOneBy(["slug" => $slug])) {
            $slug = $originalSlug . "-" . $counter;

            $counter++;
        }

        return $slug;
    }





    /**
     * Generate Article Excerpt
     * 
     * @param string Article Content
     * 
     * @return string Excerpt
     */
    private function generateArticleExcerpt(string $content): string {
        $contentHtml = (new \DBlackborough\Quill\Render($content))->render();
        $text = str_replace(['\r', '\n', '\t'], " ", \Soundasleep\Html2Text::convert($contentHtml));

        return substr($text, 0, 80) . " ...";
    }
}