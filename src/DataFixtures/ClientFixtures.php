<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private array $users = [
        'koryo',
        'silla',
        'heisei',
        'joseon'
    ];

     /**
     * passwordHasher
     *
     * @var mixed
     */
    private $passwordHasher;
  
    /**
     * __construct
     *
     * @param  UserPasswordHasherInterface $passwordHasher passwordHasher
     * @return void
     */
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getReferenceKey($key): string
    {
        return sprintf('user_%s', $key);
    }

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

        foreach ($this->users as $key => $userName) {
            $user = new User();
            $user->setName(ucfirst($userName))
                ->setEmail($userName . '@email.com')
                ->setRoles(['ROLE_USER'])
                ->setPhone($faker->phoneNumber())
                ->setDescription($faker->words(250, true));

            $password = $this->passwordHasher->hashPassword($user, 'azertyuiop');
            $user->setPassword($password);

            $manager->persist($user);
            $this->addReference(self::getReferenceKey($key), $user);
        }

        $manager->flush();
    }
}