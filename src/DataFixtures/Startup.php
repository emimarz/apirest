<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Startup extends Fixture
{
    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $allProducts = [];
        $currencyValues = Product::getCurrenciesTypes();
        for ($i = 0; $i < 100; $i++) {
            $newProduct = new Product();
            $newProduct->setName('product ' . $i);
            $newProduct->setPrice(mt_rand(1000, 10000)/100);
            $newProduct->setCurrency($currencyValues[mt_rand(0, 1)]);
            $newProduct->setFeatured((bool)mt_rand(0, 1));
            $manager->persist($newProduct);
            $manager->flush();
            $allProducts[] = $newProduct;
        }

        for ($i = 0; $i < 50; $i++) {
            $newCategory = new Category();
            $newCategory->setName('category ' . $i);
            //some din't have any description
            if ($i % 2 == 0) {
                $newCategory->setDescription('description' . mt_rand(10, 100));
            }
            $manager->persist($newCategory);
            if ($i % 5 == 0) {
                $tmpProduct = null;
                $found = false;
                for ($j = 0; $j < 5; $j++) {
                    do {
                        // para rellenar datos primero calculo un numero al azar entre los productos creados
                        $tmpKey = mt_rand(0, 100);
                        //si existe es que no fue usado antes
                        if (isset($allProducts[$tmpKey])) {
                            $found = true;
                            $tmpProduct = $allProducts[$tmpKey];
                            //lo elimino para no volverlo a usar
                            unset($allProducts[$tmpKey]);
                        }
                    } while (!$found);
                    $newCategory->addProduct($tmpProduct);
                }
            }
            $manager->persist($newCategory);
            $manager->flush();
        }
    }
}
