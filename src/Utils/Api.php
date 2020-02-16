<?php

namespace App\Utils;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;

class Api
{
    private $em;

    /**
     * Api constructor.
     * @param ObjectManager $objectManager
     * @param Request $request
     */
    public function __construct(ObjectManager $objectManager, Request $request)
    {
        if ($request->headers->get("Access-Token") === null) {
            //throw new
        }
    }
}
