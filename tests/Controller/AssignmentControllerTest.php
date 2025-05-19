<?php

namespace App\Tests\Controller;

use App\Entity\Assignment;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AssignmentControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $assignmentRepository;
    private string $path = '/dashboard/assignment/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->assignmentRepository = $this->manager->getRepository(Assignment::class);

        foreach ($this->assignmentRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Assignment index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'assignment[title]' => 'Testing',
            'assignment[description]' => 'Testing',
            'assignment[isActive]' => 'Testing',
            'assignment[expiresAt]' => 'Testing',
            'assignment[targetUrl]' => 'Testing',
            'assignment[points]' => 'Testing',
            'assignment[type]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->assignmentRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Assignment();
        $fixture->setTitle('My Title');
        $fixture->setDescription('My Title');
        $fixture->setIsActive('My Title');
        $fixture->setExpiresAt('My Title');
        $fixture->setTargetUrl('My Title');
        $fixture->setPoints('My Title');
        $fixture->setType('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Assignment');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Assignment();
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        $fixture->setIsActive('Value');
        $fixture->setExpiresAt('Value');
        $fixture->setTargetUrl('Value');
        $fixture->setPoints('Value');
        $fixture->setType('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'assignment[title]' => 'Something New',
            'assignment[description]' => 'Something New',
            'assignment[isActive]' => 'Something New',
            'assignment[expiresAt]' => 'Something New',
            'assignment[targetUrl]' => 'Something New',
            'assignment[points]' => 'Something New',
            'assignment[type]' => 'Something New',
        ]);

        self::assertResponseRedirects('/dashboard/assignment/');

        $fixture = $this->assignmentRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getIsActive());
        self::assertSame('Something New', $fixture[0]->getExpiresAt());
        self::assertSame('Something New', $fixture[0]->getTargetUrl());
        self::assertSame('Something New', $fixture[0]->getPoints());
        self::assertSame('Something New', $fixture[0]->getType());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Assignment();
        $fixture->setTitle('Value');
        $fixture->setDescription('Value');
        $fixture->setIsActive('Value');
        $fixture->setExpiresAt('Value');
        $fixture->setTargetUrl('Value');
        $fixture->setPoints('Value');
        $fixture->setType('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/dashboard/assignment/');
        self::assertSame(0, $this->assignmentRepository->count([]));
    }
}
