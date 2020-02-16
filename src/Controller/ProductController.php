<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Form\ProductType;
use Doctrine\ORM\EntityManager;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\View\View as RestView;
use Nelmio\ApiDocBundle\Annotation\Model;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Class ProductController
 * @package App\Controller
 * @Rest\RouteResource("product", pluralize=false)
 */
class ProductController extends AbstractFOSRestController
{
    /**
     * List of Products
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all Products",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Product::class, groups={"full"}))
     *     )
     * )
     * @SWG\Tag(name="products")
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
            'products' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $products = $em->getRepository('App:Product')->findAll();

            // I know there are another way, configuring fos bundle but to be minimalistic I do this
            /** @var Product $product */
            foreach ($products as $product) {
                $result['products'][] = ($product->getData(true));
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
     * Get one Product
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns one Product",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Product::class)
     *     )
     * )
     * @SWG\Tag(name="products")
     *
     * @ParamConverter("product", converter="doctrine.orm", isOptional=true)
     *
     * @param Request $request
     * @param Product|null $product
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function getAction(Request $request, Product $product): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'product' => [],
        ];
        try {
            if (empty($product)) {
                throw new Exception(sprintf("Product could not be found."), JsonResponse::HTTP_NOT_FOUND);
            }

            // I know there are another way, configuring fos bundle but to be minimalistic I do this
            $result['product'] = ($product->getData(true));
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
     * Create a new Product
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Create one Product",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Product::class)
     *     )
     * )
     * @SWG\Tag(name="products")
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
            'product' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $newProduct = new Product();
            $data = json_decode($request->getContent(), true);

            $category = $em->getRepository(Category::class)->findOneBy(['id' => ($data['category'] ?? '')]);

            if (is_object($category)) {
                $newProduct->setCategory($category);
            }

            $form = $this->createForm(ProductType::class, $newProduct);
            $form->submit($data, false);

            if ($form->isValid() === false) {
                throw new Exception('Validation error', JsonResponse::HTTP_BAD_REQUEST);
            }

            if (!isset($data['featured']) || !is_bool($data['featured'])) {
                $newProduct->setFeatured(false);
            }

            $em->persist($newProduct);
            $em->flush();
            $result['product'] = $newProduct->getData();
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
     * Edit one Product
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Edit one Product",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Product::class)
     *     )
     * )
     * @SWG\Tag(name="products")
     *
     * @param Request $request
     * @param Product $product |null
     *
     * @ParamConverter("product", converter="doctrine.orm", isOptional=true)
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function putAction(Request $request, Product $product): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'product' => [],
        ];
        $em = $this->getDoctrine()->getManager();

        try {
            $data = json_decode($request->getContent(), true);
            $category = $em->getRepository(Category::class)->findOneBy(['id' => ($data['category'] ?? '')]);

            if (is_object($category)) {
                $product->setCategory($category);
            } elseif (key_exists('category', $data) && $data['category'] === null) {
                $product->setCategory(null);
            }

            $form = $this->createForm(ProductType::class, $product);
            $form->submit($data, false);

            if ($form->isValid() === false) {
                // I could display the error given by the form but I don't want reveal information
                throw new Exception('Validation error', JsonResponse::HTTP_BAD_REQUEST);
            }

            if (isset($data['featured'])) {
                $product->setFeatured($data['featured']);
            }

            $em->persist($product);
            $em->flush();
            $result['product'] = $product->getData();
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
     * Delete Product
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Delete Product",
     *     @SWG\Schema(
     *         type="object",
     *         @Model(type=Product::class)
     *     )
     * )
     * @SWG\Tag(name="products")
     *
     * @param Request $request
     * @param Product $product |null
     *
     * @ParamConverter("product", converter="doctrine.orm", isOptional=true)
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     *
     * @return RestView
     */
    public function deleteAction(Request $request, Product $product): RestView
    {
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
        ];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        try {
            if (is_null($product)) {
                throw new Exception('Product not found', JsonResponse::HTTP_NOT_FOUND);
            }

            $em->remove($product);
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
     * List of Products featured
     *
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns all Products featured",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Product::class, groups={"full"}))
     *     )
     * )
     * @SWG\Tag(name="products")
     *
     * @View(
     *  serializerGroups={"list_public"}
     * )
     *
     * @param Request $request
     *
     * @return RestView
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function cgetFeaturedAction(Request $request): RestView
    {
        $httpClient = HttpClient::create();
        $result = [
            'code' => JsonResponse::HTTP_OK,
            'message' => 'OK',
            'products' => [],
        ];
        $currency = $request->get('currency', 'USD');
        $em = $this->getDoctrine()->getManager();

        try {
            if (!in_array($currency, Product::getCurrenciesTypes())) {
                throw new Exception('Currency not found', JsonResponse::HTTP_BAD_REQUEST);
            }
            $res = $httpClient->request('GET', 'https://api.exchangeratesapi.io/latest?base=USD&symbols=EUR');
            $res = json_decode($res->getContent(), true);
            $convertEuros = $res['rates']['EUR'];
            $res = $httpClient->request('GET', 'https://api.exchangeratesapi.io/latest?base=EUR&symbols=USD');
            $res = json_decode($res->getContent(), true);
            $convertUSD = $res['rates']['USD'];

            $products = $em->getRepository('App:Product')->findBy([
                'featured' => true
            ]);

            // I know there are another way, configuring fos bundle but to be minimalistic I do this
            /** @var Product $product */
            foreach ($products as $product) {
                $result['products']['id'] = $product->getId();
                $result['products']['name'] = $product->getName();
                $price = ($product->getCurrency() == $currency) ? $product->getPrice() : (($currency == 'EUR') ? ($convertEuros * $product->getPrice()) : ($convertUSD * $product->getPrice()));
                $result['products']['price'] = round($price, 2);
                $result['products']['currency'] = $currency;
                $result['products']['category.name'] = ($product->getCategory() != null) ? $product->getCategory()->getName() : null;
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
}
