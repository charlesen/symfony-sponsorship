<?php

namespace App\Tests\Controller;

use App\Entity\AssignmentType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AssignmentTypeControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $assignmentTypeRepository;
    private string $path = '/dashboard/assignment/type/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->assignmentTypeRepository = $this->manager->getRepository(AssignmentType::class);

        foreach ($this->assignmentTypeRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('AssignmentType index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'assignment_type[title]' => 'Testing',
            'assignment_type[code]' => 'Testing',
            'assignment_type[description]' => 'Testing',
            'assignment_type[icon]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->assignmentTypeRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new AssignmentType();
        $fixture->setTitle('My Title');
        $fixture->setCode('My Title');
        $fixture->setDescription('My Title');
        $fixture->setIcon('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('AssignmentType');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new AssignmentType();
        $fixture->setTitle('Value');
        $fixture->setCode('Value');
        $fixture->setDescription('Value');
        $fixture->setIcon('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'assignment_type[title]' => 'Something New',
            'assignment_type[code]' => 'Something New',
            'assignment_type[description]' => 'Something New',
            'assignment_type[icon]' => 'Something New',
        ]);

        self::assertResponseRedirects('/dashboard/assignment/type/');

        $fixture = $this->assignmentTypeRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getCode());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getIcon());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new AssignmentType();
        $fixture->setTitle('Value');
        $fixture->setCode('Value');
        $fixture->setDescription('Value');
        $fixture->setIcon('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/dashboard/assignment/type/');
        self::assertSame(0, $this->assignmentTypeRepository->count([]));
    }
}
