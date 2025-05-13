<?php

namespace App\Trait;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\String\Slugger\AsciiSlugger;

trait Sluggable
{
    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function generateUniqueSlug(string $title, callable $existsCheck): string
    {
        $slugger = new AsciiSlugger('fr');
        $baseSlug = $slugger->slug(strtolower($title))->toString();
        $uniqueSlug = $baseSlug;
        $counter = 1;

        while ($existsCheck($uniqueSlug)) {
            $uniqueSlug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $this->setSlug($uniqueSlug);

        return $uniqueSlug;
    }
}
