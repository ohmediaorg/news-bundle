<?php

namespace OHMedia\NewsBundle\Entity;

use OHMedia\NewsBundle\Repository\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __toString(): string
    {
        return 'Article #'.$this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
