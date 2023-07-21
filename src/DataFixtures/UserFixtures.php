<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * load
     *
     * @param  ObjectManager $manager Manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->seed(2);

        for ($i = 0; $i < 50; $i++) {
            $user = new User();

            $client = $this->getReference(ClientFixtures::getReferenceKey($i % 4));

            $user->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setEmail('user' . $i . '@' . $client . '.com')
                ->setCreatedBy($client);

                $manager->persist($user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ClientFixtures::class];
    }
}
