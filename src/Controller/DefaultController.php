<?php

namespace App\Controller;

use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View as RestView;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractFOSRestController
{
    /**
     * @Route("/", name="default")
     * @param Request $request
     * @return RestView
     */
    public function index(Request $request)
    {
        $return = [
            'code' => JsonResponse::HTTP_NOT_FOUND,
            'message' => 'NOT FOUND',
        ];

        return $this->view($return, $return['code']);
    }

    /**
     * @param Request $request
     * @param Exception $exception
     * @return RestView
     */
    public function error(Request $request, Exception $exception)
    {
        $return = [
            'code' => JsonResponse::HTTP_NOT_FOUND,
            'message' => 'NOT FOUND',
        ];
        $ret = strpos($exception->getMessage(), '@ParamConverter annotation');
        if ($ret === false) {
            $return['message'] = $exception->getMessage();
        }

        return $this->view($return, $return['code']);
    }
}
