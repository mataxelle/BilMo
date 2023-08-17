<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Member;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class MemberFixtures extends Fixture implements DependentFixtureInterface
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
            $member = new Member();

            $user = $this->getReference(UserFixtures::getReferenceKey($i % 4));

            $member->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setEmail('member' . $i . '@' . $user . '.com')
                ->setCreatedBy($user);

                $manager->persist($member);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
