<?php

namespace App\DataFixtures;

use App\Entity\Brand;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class BrandFixtures extends Fixture
{
    private array $brands =
    [
        'Samsung',
        'Apple',
        'Huawei',
        'Asus',
        'Xiaomi',
        'Google',
        'Oppo'
    ];

    public static function getReferenceKey($key): string
    {
        return sprintf('brand_%s', $key);
    }

    /**
     * Loading
     *
     * @param  ObjectManager $manager Manager
     *
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->brands as $key => $brandName) {
            $brand = new Brand();
            $brand->setName(ucfirst($brandName));

            $manager->persist($brand);
            $this->addReference(self::getReferenceKey($key), $brand);
        }

        $manager->flush();
    }
}
