<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManager;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\View\View as RestView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CategoryController
 * @package App\Controller
 * @Rest\RouteResource("category", pluralize=false)
 */
//* @Route("/",name="api_")
class CategoryController extends AbstractFOSRestController
{
    /**
     * List of Categories
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all Categories",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Category::class, groups={"full"}))
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     * @param Request $request
     *
     * @return RestView
     */
    public function cgetAction(Request $request): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'categories' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $categories = $em->getRepository('App:Category')->findAll();

            // I know there are another way, configuring fos bundle but to be minimalistic I do this
            /** @var Category $category */
            foreach ($categories as $category) {
                $result['categories'][] = ($category->getData());
            }
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }

        return $this->view(
            $result,
            $result['code']
        );
    }

    /**
     * Get one Category
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns one Category",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @ParamConverter("category", converter="doctrine.orm", isOptional=true)
     *
     * @param Request $request
     * @param Category|null $category
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function getAction(Request $request, Category $category): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'category' => [],
        ];
        try {
            if (empty($category)) {
                throw new Exception(sprintf("Category could not be found."), JsonResponse::HTTP_NOT_FOUND);
            }

            // I know there are another way, configuring fos bundle but to be minimalistic I do this
            $result['category'] = ($category->getData());
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }

        return $this->view(
            $result,
            $result['code']
        );
    }

    /**
     * Edit one Category
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Edit one Category",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @param Request $request
     * @param Category $category |null
     *
     * @ParamConverter("category", converter="doctrine.orm", isOptional=true)
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function putAction(Request $request, Category $category): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'category' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $data = json_decode($request->getContent(), true);
            $form = $this->createForm(CategoryType::class, $category);
            $form->submit($data, true);

            if ($form->isValid() === false) {
                // I could display the error given by the form but I don't want reveal information
                throw new Exception('Validation error', JsonResponse::HTTP_BAD_REQUEST)
            }

            $em->persist($category);
            $em->flush();
            $result['category'] = $category->getData();
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }


        return $this->view(
            $result,
            $result['code']
        );
    }

    /**
     * Create a new Category
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Create one Category",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @param Request $request
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function postAction(Request $request): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'category' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $newCategory = new Category();
            $data = json_decode($request->getContent(), true);

            $form = $this->createForm(CategoryType::class, $newCategory);
            $form->submit($data, true);

            if ($form->isValid() === false) {
                throw new Exception('Validation error', JsonResponse::HTTP_BAD_REQUEST);
            }

            $em->persist($newCategory);
            $em->flush();
            $result['category'] = $newCategory->getData();
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }


        return $this->view(
            $result,
            $result['code']
        );
    }

    /**
     * Delete Category
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete Category",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @param Request $request
     * @param Category $category |null
     *
     * @ParamConverter("category", converter="doctrine.orm", isOptional=true)
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function deleteAction(Request $request, Category $category): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
        ];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        try {
            if (is_null($category)) {
                throw new Exception('Category not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $em->remove($category);
            $em->flush();
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }


        return $this->view(
            $result,
            $result['code']
        );
    }

    /**
     * get all products associate to one Category
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="All Products from Category",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @param Request $request
     * @param Category $category |null
     *
     * @ParamConverter("category", converter="doctrine.orm", isOptional=true)
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function getProductsAction(Request $request, Category $category): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'products' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $products = $em->getRepository('App:Product')->findBy(['category' => $category]);

            // I know there are another way, configuring fos bundle but to be minimalistic I do this
            /** @var Product $product */
            foreach ($products as $product) {
                $result['products'][] = ($product->getData());
            }
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }

        return $this->view(
            $result,
            $result['code']
        );
    }

    /**
     * associate one product to one Category
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Associate products and Categories",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @param Request $request
     * @param Category $category |null
     * @param Product $product |null
     *
     * @ParamConverter("category", converter="doctrine.orm", isOptional=true)
     * @ParamConverter("product", converter="doctrine.orm", isOptional=true)
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function patchProductAction(Request $request, Category $category, Product $product): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'category' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $category->addProduct($product);
            $em->persist($category);
            $em->flush();
            $result['category'] = ($category->getData());
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }

        return $this->view(
            $result,
            $result['code']
        );
    }

    /**
     * delete association between product and Category
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Disassociate products and Categories",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Category::class)
     *     )
     * )
     * @SWG\Tag(name="categories")
     *
     * @param Request $request
     * @param Category $category |null
     * @param Product $product |null
     *
     * @ParamConverter("category", converter="doctrine.orm", isOptional=true)
     * @ParamConverter("product", converter="doctrine.orm", isOptional=true)
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function deleteProductAction(Request $request, Category $category, Product $product): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'category' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $category->removeProduct($product);
            $em->persist($category);
            $em->flush();
            $result['category'] = ($category->getData());
        } catch (Exception $e) {
            $result['code'] = ($e->getCode() == 0) ? JsonResponse::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
            $result['message'] = $e->getMessage();
        }

        return $this->view(
            $result,
            $result['code']
        );
    }

}
