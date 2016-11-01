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

use BernardoSecades\Json\Json;
use BernardoSecades\Json\DecodeException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\AbstractType;
use JMS\Serializer\Serializer;

/**
 * Class AbstractBaseController
 * @package Demo\AppBundle\Controller
 */
abstract class AbstractBaseController extends Controller
{
    /**
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return Response
     */
    protected function getResponse($data, $statusCode, array $headers = [])
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('jms_serializer');
        $dataSerialized = $serializer->serialize($data, 'json');

        $response = new Response($dataSerialized, $statusCode, $headers);
        $response->headers->add($headers);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Check if data is valid and save in object entity.
     *
     * @param AbstractType $type
     * @param mixed        $entity
     * @param Request      $request
     *
     * @return array
     */
    protected function processForm(AbstractType $type, $entity, Request $request)
    {
        $errors = [];
        try {
            $data = Json::decode($request->getContent(), true);
        } catch (DecodeException $exception) {
            $errors[] = sprintf('%s, ErrorCode: %d', $exception->getMessage(), $exception->getCode());

            return $errors;
        }

        $form = $this->createForm($type, $entity);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = $this->getAllFormErrors($form->all());
        }

        return $errors;
    }

    /**
     * @param FormInterface[] $children
     *
     * @return array
     */
    private function getAllFormErrors($children)
    {
        $allErrors = [];
        foreach ($children as $child) {
            if (!empty($child->all())) {
                $allErrors[$child->getName()] = $this->getAllFormErrors($child->all());
            } elseif (!empty($child->getErrors())) {
                $errors = $child->getErrors();
                foreach ($errors as $error) {
                    $allErrors[$child->getName()] = $error->getMessage();
                }
            }
        }

        return $allErrors;
    }
}
