<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CategoryFixtures extends Fixture
{
    private array $categories = [
        'iPhone',
        'smartphone',
        'accessories',
        'cases & Covers',
        'tablets',
        'power banks',
        'refurbished & open box',
        'wearable devices'
    ];

    public static function getReferenceKey($key): string
    {
        return sprintf('category_%s', $key);
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->categories as $key => $categoryName) {
            $category = new Category();
            $category->setName(ucfirst($categoryName));

            $manager->persist($category);
            $this->addReference(self::getReferenceKey($key), $category);
        }

        $manager->flush();
    }
}