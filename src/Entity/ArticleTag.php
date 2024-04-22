<?php

namespace OHMedia\NewsBundle\Entity;

use OHMedia\NewsBundle\Repository\ArticleTagRepository;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;

#[ORM\Entity(repositoryClass: ArticleTagRepository::class)]
class ArticleTag
{
    use BlameableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function __toString(): string
    {
        return 'Article Tag #'.$this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
