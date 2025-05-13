<?php

namespace App\EventListener;

use App\Contract\SluggableEntityInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist)]
#[AsEntityListener(event: Events::preUpdate)]
final class SluggableListener
{
    public function prePersist(PrePersistEventArgs $event): void
    {
        $this->handleSlug($event);
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $this->handleSlug($event);
    }

    private function handleSlug(PrePersistEventArgs|PreUpdateEventArgs $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof SluggableEntityInterface || !method_exists($entity, 'generateUniqueSlug')) {
            return;
        }

        $em = $event->getObjectManager();
        $repo = $em->getRepository(get_class($entity));

        $existsCheck = fn(string $slug) => (bool) $repo->findOneBy(['slug' => $slug]);

        if (empty($entity->getSlug())) {
            $slug = $entity->generateUniqueSlug($entity->getTitle(), $existsCheck);
            $entity->setSlug($slug);
        }
    }
}
