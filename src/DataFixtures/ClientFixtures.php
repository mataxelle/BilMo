<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Client;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientFixtures extends Fixture
{
    private array $clients = [
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
        return sprintf('client_%s', $key);
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

        foreach ($this->clients as $key => $clientName) {
            $client = new Client();
            $client->setName(ucfirst($clientName))
                ->setEmail($clientName . '@email.com')
                ->setRoles(['ROLE_USER'])
                ->setPhone($faker->phoneNumber())
                ->setDescription($faker->words(250, true));

            $password = $this->passwordHasher->hashPassword($client, 'azertyuiop');
            $client->setPassword($password);

            $manager->persist($client);
            $this->addReference(self::getReferenceKey($key), $client);
        }

        $manager->flush();
    }
}