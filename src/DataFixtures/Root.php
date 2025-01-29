<?php

namespace App\DataFixtures;

use App\Entity\AccessRight;
use App\Entity\Folder;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class Root extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $newfolder=new Folder();
        $newfolder->setName('root');

        $WriteAccesRight=new AccessRight();
        $WriteAccesRight->setFolder($newfolder);
        $WriteAccesRight->setMode(2);
        $newfolder->addAccessRight($WriteAccesRight);

        $ReadAccesRight=new AccessRight();
        $ReadAccesRight->setFolder($newfolder);
        $ReadAccesRight->setMode(1);
        $newfolder->addAccessRight($ReadAccesRight);
        $manager->persist($newfolder);

        

        $manager->flush();
    }
}
