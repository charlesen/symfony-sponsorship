<?php

namespace App\Tests\Controller;

use App\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PageControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $pageRepository;
    private string $path = '/dashboard/page/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->pageRepository = $this->manager->getRepository(Page::class);

        foreach ($this->pageRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Page index');
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $now = new \DateTimeImmutable();
        $this->client->submitForm('Save', [
            'page[title]' => 'Testing',
            'page[content]' => 'Testing',
            'page[status]' => 'Testing',
            'page[createdAt]' => $now,
            'page[updatedAt]' => $now,
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->pageRepository->count([]));
    }

    public function testShow(): void
    {
        $now = new \DateTimeImmutable();
        $this->markTestIncomplete();
        $fixture = new Page();
        $fixture->setTitle('My Title');
        $fixture->setContent('My Title');
        $fixture->setStatus('My Title');
        $fixture->setCreatedAt($now);
        $fixture->setUpdatedAt($now);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Page');
    }

    public function testEdit(): void
    {
        $now = new \DateTimeImmutable();
        $this->markTestIncomplete();
        $fixture = new Page();
        $fixture->setTitle('Value');
        $fixture->setContent('Value');
        $fixture->setStatus('Value');
        $fixture->setCreatedAt($now);
        $fixture->setUpdatedAt($now);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'page[title]' => 'Something New',
            'page[content]' => 'Something New',
            'page[status]' => 'Something New',
            'page[createdAt]' => $now,
            'page[updatedAt]' => $now,
        ]);

        self::assertResponseRedirects('/dashboard/page/');

        $fixture = $this->pageRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitle());
        self::assertSame('Something New', $fixture[0]->getContent());
        self::assertSame('Something New', $fixture[0]->getStatus());
        self::assertSame($now, $fixture[0]->getCreatedAt());
        self::assertSame($now, $fixture[0]->getUpdatedAt());
    }

    public function testRemove(): void
    {
        $now = new \DateTimeImmutable();
        $this->markTestIncomplete();
        $fixture = new Page();
        $fixture->setTitle('Value');
        $fixture->setContent('Value');
        $fixture->setStatus('Value');
        $fixture->setCreatedAt($now);
        $fixture->setUpdatedAt($now);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/dashboard/page/');
        self::assertSame(0, $this->pageRepository->count([]));
    }
}
