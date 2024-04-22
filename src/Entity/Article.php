<?php

namespace OHMedia\NewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use OHMedia\FileBundle\Entity\File;
use OHMedia\MetaBundle\Entity\Meta;
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

    #[ORM\Column(length: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $author = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $snippet = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?File $image = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meta $meta = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $publish_datetime = null;

    /**
     * @var Collection<int, ArticleTag>
     */
    #[ORM\ManyToMany(targetEntity: ArticleTag::class)]
    private Collection $ArticleTag;

    public function __construct()
    {
        $this->ArticleTag = new ArrayCollection();
    }

    public function __toString(): string
    {
        return 'Article #'.$this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getSnippet(): ?string
    {
        return $this->snippet;
    }

    public function setSnippet(string $snippet): static
    {
        $this->snippet = $snippet;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(File $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getMeta(): ?Meta
    {
        return $this->meta;
    }

    public function setMeta(Meta $meta): static
    {
        $this->meta = $meta;

        return $this;
    }

    public function getPublishDatetime(): ?\DateTimeInterface
    {
        return $this->publish_datetime;
    }

    public function setPublishDatetime(\DateTimeInterface $publish_datetime): static
    {
        $this->publish_datetime = $publish_datetime;

        return $this;
    }

    /**
     * @return Collection<int, ArticleTag>
     */
    public function getArticleTag(): Collection
    {
        return $this->ArticleTag;
    }

    public function addArticleTag(ArticleTag $articleTag): static
    {
        if (!$this->ArticleTag->contains($articleTag)) {
            $this->ArticleTag->add($articleTag);
        }

        return $this;
    }

    public function removeArticleTag(ArticleTag $articleTag): static
    {
        $this->ArticleTag->removeElement($articleTag);

        return $this;
    }
}
