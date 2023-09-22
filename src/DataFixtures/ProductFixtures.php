<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Loading
     *
     * @param ObjectManager $manager Manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // Faker
        $faker = Factory::create('fr_FR');
        // To keep the same fixtures
        $faker->seed(2);

        for ($i = 0; $i < 50; $i++) {
            $product = new Product();

            $brand = $this->getReference(BrandFixtures::getReferenceKey($i % 7));
            $category = $this->getReference(CategoryFixtures::getReferenceKey($i % 8));

            $product->setName(ucfirst($faker->word()))
                ->setBrand($brand)
                ->setDescription($faker->text())
                ->setPrice($faker->randomFloat(2, 8, 5000))
                ->setSku($faker->ean13())
                ->setCategory($category)
                ->setAvailable($faker->boolean(50));

                $manager->persist($product);
        }

        $manager->flush();
    }

    /**
     * GetDependencies
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [BrandFixtures::class, CategoryFixtures::class];
    }
}
