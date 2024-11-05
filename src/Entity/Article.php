<?php

namespace OHMedia\NewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Entity\File;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\UtilityBundle\Entity\BlameableEntityTrait;
use OHMedia\UtilityBundle\Entity\SluggableEntityInterface;
use OHMedia\UtilityBundle\Entity\SluggableEntityTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity('slug')]
#[UniqueEntity('title')]
class Article implements SluggableEntityInterface
{
    use BlameableEntityTrait;
    use SluggableEntityTrait;

    public const SETTING_RSS_TITLE = 'news_rss_title';
    public const SETTING_RSS_DESC = 'news_rss_desc';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $author = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $snippet = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?File $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $publish_at = null;

    /**
     * @var Collection<int, ArticleTag>
     */
    #[ORM\ManyToMany(targetEntity: ArticleTag::class, inversedBy: 'articles')]
    private Collection $tags;

    private ?\DateTimeZone $timezone = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->title;
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

    public function getLocalPublishDatetime(): ?\DateTimeInterface
    {
        if ($this->publish_at && $this->timezone) {
            $publish_at = clone $this->publish_at;
            $publish_at->setTimezone($this->timezone);

            return $publish_at;
        }

        return $this->publish_at;
    }

    public function getPublishAt(): ?\DateTimeInterface
    {
        return $this->publish_at;
    }

    public function setPublishAt(?\DateTimeInterface $publish_at): static
    {
        $this->publish_at = $publish_at;

        return $this;
    }

    /**
     * @return Collection<int, ArticleTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(ArticleTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(ArticleTag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function isDraft(): bool
    {
        return is_null($this->publish_at);
    }

    public function isPublished(): bool
    {
        return !$this->isDraft() && $this->publish_at <= new \DateTime();
    }

    public function isScheduled(): bool
    {
        return !$this->isDraft() && $this->publish_at > new \DateTime();
    }

    public function getTimezone(): ?\DateTimeZone
    {
        return $this->timezone;
    }

    public function setTimezone(?\DateTimeZone $timezone): static
    {
        $this->timezone = $timezone;

        return $this;
    }
}
