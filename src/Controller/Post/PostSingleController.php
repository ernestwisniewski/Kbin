<?php declare(strict_types=1);

namespace App\Controller\Post;

use App\Controller\AbstractController;
use App\Entity\Magazine;
use App\Entity\Post;
use App\Event\Post\PostHasBeenSeenEvent;
use App\PageView\PostCommentPageView;
use App\Repository\Criteria;
use App\Repository\PostCommentRepository;
use Pagerfanta\PagerfantaInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostSingleController extends AbstractController
{
    /**
     * @ParamConverter("magazine", options={"mapping": {"magazine_name": "name"}})
     * @ParamConverter("post", options={"mapping": {"post_id": "id"}})
     */
    public function __invoke(
        Magazine $magazine,
        Post $post,
        PostCommentRepository $repository,
        EventDispatcherInterface $dispatcher,
        Request $request
    ): Response {
        $criteria = new PostCommentPageView($this->getPageNb($request));
        $criteria->sortOption = Criteria::SORT_NEW;
        $criteria->post = $post;

        $comments = $repository->findByCriteria($criteria);

        $dispatcher->dispatch((new PostHasBeenSeenEvent($post)));

        if ($request->isXmlHttpRequest()) {
            return $this->getJsonResponse($magazine, $post, $comments);
        }

        return $this->render(
            'post/single.html.twig',
            [
                'magazine' => $magazine,
                'post'     => $post,
                'comments' => $comments,
            ]
        );
    }

    private function getJsonResponse(Magazine $magazine, Post $post, PagerfantaInterface $comments): JsonResponse
    {
        return new JsonResponse(
            [
                'html' => $this->renderView(
                    'post/_single_popup.html.twig',
                    [
                        'magazine' => $magazine,
                        'post'     => $post,
                        'comments' => $comments,
                    ]
                ),
            ]
        );
    }
}
