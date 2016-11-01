<?php

/**
 * MIT License
 *
 * Copyright (c) 2016 Bernardo Secades
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Demo\AppBundle\Controller;

use Demo\Entity\Comment;
use Demo\ValueObject\ErrorCode;
use Demo\ValueObject\ErrorResponse;
use Demo\AppBundle\Form\ScoreType;
use Demo\AppBundle\Request\ScoreRequest;
use Demo\BusinessCase\Exception\InvalidVoteException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractBaseController
{
    /**
     * Get total total rating Comment.
     *
     * @Route("/{id}/rating", requirements={ "id": "\d+" })
     * @Method("GET")
     *
     * @ApiDoc(
     *  section      = "Rating",
     *  resource     = true,
     *  views        = { "default" },
     *  deprecated   = false,
     *  statusCodes  = {
     *     200 = "OK.",
     *     404 = "The comment does not exist."
     *  }
     * )
     *
     * @param Comment $comment
     *
     * @return JsonResponse
     */
    public function getTotalScore(Comment $comment = null)
    {
        if (null === $comment) {
            return $this->getResponse(new ErrorResponse(['Comment does not exist'], ErrorCode::RESOURCE_NOT_EXIST()), Response::HTTP_NOT_FOUND);
        }

        return $this->getResponse($this->get('demo.repository.rating')->getTotalRating($comment), Response::HTTP_OK);
    }

    /**
     * Create a new score Comment.
     *
     * @Route("/{id}/rating", requirements={ "id": "\d+" })
     * @Method("POST")
     *
     * @ApiDoc(
     *  section     = "Rating",
     *  resource    = true,
     *  input       = "Demo\AppBundle\Form\ScoreType",
     *  output      = "Demo\Entity\Rating",
     *  views       = { "default" },
     *  deprecated  = false,
     *  statusCodes = {
     *     200 = "The score was successfully created.",
     *     404 = "The comment does not exist.",
     *     400 = "Incorrect input."
     *  }
     * )
     *
     * @param Comment $comment
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function createScore(Comment $comment = null, Request $request)
    {
        if (null === $comment) {
            return $this->getResponse(new ErrorResponse(['Comment does not exist'], ErrorCode::COMMENT_NOT_EXIST()), Response::HTTP_NOT_FOUND);
        }

        $scoreRequest = new ScoreRequest();
        $errors = $this->processForm(new ScoreType(), $scoreRequest, $request);

        if (!empty($errors)) {
            return $this->getResponse(new ErrorResponse($errors, ErrorCode::INVALID_PARAMETERS()), Response::HTTP_BAD_REQUEST);
        }

        $user    = $this->get('demo.repository.user')->getUser($scoreRequest->userId);
        if (null === $user) {
            return $this->getResponse(new ErrorResponse(['User does not exist'], ErrorCode::USER_NOT_EXIST()), Response::HTTP_BAD_REQUEST);
        }

        try {
            $rating = $this->get('demo.rating_businesscase')->save($comment, $user, $scoreRequest->score);
        } catch (InvalidVoteException $exception) {
            return $this->getResponse(new ErrorResponse([$exception->getMessage()], ErrorCode::INVALID_VOTE()), Response::HTTP_BAD_REQUEST);
        }

        return $this->getResponse($rating, Response::HTTP_OK);
    }
}
