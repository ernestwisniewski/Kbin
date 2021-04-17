<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture
{
    const USERS_COUNT = 21;

    public function __construct(private UserPasswordEncoderInterface $encoder)
    {
    }

    public function loadData(ObjectManager $manager): void
    {
        foreach ($this->provideRandomUsers(self::USERS_COUNT) as $index => $user) {
            $newUser = new User(
                $user['email'],
                $user['username'],
                $user['password']
            );

            $newUser->setPassword(
                $this->encoder->encodePassword($newUser, $user['password'])
            );

            $manager->persist($newUser);

            $this->addReference('user'.'_'.$index, $newUser);
        }

        $manager->flush();
    }

    private function provideRandomUsers($count = 1): iterable
    {
        yield [
            'email'    => 'demo@karab.in',
            'username' => 'demo',
            'password' => 'demo',
        ];

        for ($i = 0; $i <= $count; $i++) {
            yield [
                'email'    => $this->faker->email,
                'username' => str_replace('.', '_', $this->faker->userName),
                'password' => 'secret',
            ];
        }
    }
}
