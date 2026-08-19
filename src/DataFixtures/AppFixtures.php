<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // 1. Top Ambassador: Alice (Platinum Tier)
        $alice = new User();
        $alice->setEmail('alice@example.com');
        $alice->setFirstname('Alice');
        $alice->setLastname('Dupont');
        $alice->setReferrerCode('ALICE100');
        $alice->setPoints(1850);
        $alice->setReferralClicks(84);
        $alice->setRoles(['ROLE_USER']);
        $manager->persist($alice);

        // 2. Gold Ambassador: Bob (Gold Tier)
        $bob = new User();
        $bob->setEmail('bob@example.com');
        $bob->setFirstname('Bob');
        $bob->setLastname('Martin');
        $bob->setReferrerCode('BOB20000');
        $bob->setPoints(850);
        $bob->setReferralClicks(42);
        $bob->setRoles(['ROLE_USER']);
        $manager->persist($bob);

        // 3. Silver Champion: Chloe (Silver Tier)
        $chloe = new User();
        $chloe->setEmail('chloe@example.com');
        $chloe->setFirstname('Chloé');
        $chloe->setLastname('Bernard');
        $chloe->setReferrerCode('CHLOE300');
        $chloe->setPoints(350);
        $chloe->setReferralClicks(19);
        $chloe->setRoles(['ROLE_USER']);
        $manager->persist($chloe);

        // 4. Bronze Supporter: David (Bronze Tier)
        $david = new User();
        $david->setEmail('david@example.com');
        $david->setFirstname('David');
        $david->setLastname('Petit');
        $david->setReferrerCode('DAVID400');
        $david->setPoints(70);
        $david->setReferralClicks(7);
        $david->setRoles(['ROLE_USER']);
        $manager->persist($david);

        // 5. Standard Test User
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFirstname('John');
        $user->setLastname('Doe');
        $user->setReferrerCode('JOHNDOE1');
        $user->setPoints(20);
        $user->setReferralClicks(3);
        $user->setRoles(['ROLE_USER']);
        $user->setReferrer($chloe);
        $manager->persist($user);

        // Add 12 dummy referrals for Alice
        for ($i = 1; $i <= 12; $i++) {
            $ref = new User();
            $ref->setEmail(sprintf('alice.friend%d@example.com', $i));
            $ref->setFirstname(sprintf('Friend%d', $i));
            $ref->setReferrerCode(sprintf('REFAL%03d', $i));
            $ref->setRoles(['ROLE_USER']);
            $ref->setReferrer($alice);
            $manager->persist($ref);
        }

        // Add 6 dummy referrals for Bob
        for ($i = 1; $i <= 6; $i++) {
            $ref = new User();
            $ref->setEmail(sprintf('bob.friend%d@example.com', $i));
            $ref->setFirstname(sprintf('BobFriend%d', $i));
            $ref->setReferrerCode(sprintf('REFBO%03d', $i));
            $ref->setRoles(['ROLE_USER']);
            $ref->setReferrer($bob);
            $manager->persist($ref);
        }

        // 6. Administrator
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('System');
        $admin->setReferrerCode('ADMIN001');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPoints(500);
        $manager->persist($admin);

        $manager->flush();
    }
}
