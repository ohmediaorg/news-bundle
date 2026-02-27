<?php

namespace OHMedia\NewsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\FileBundle\Entity\File;
use OHMedia\NewsBundle\Repository\ArticleRepository;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\UtilityBundle\Entity\BlameableEntityTrait;
use OHMedia\UtilityBundle\Entity\SluggableEntityInterface;
use OHMedia\UtilityBundle\Entity\SluggableEntityTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity('slug')]
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
    #[Assert\Length(max: 100)]
    private ?string $title = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $author = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $snippet = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[Assert\Valid]
    private ?File $image = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $published_at = null;

    /**
     * @var Collection<int, ArticleTag>
     */
    #[ORM\ManyToMany(targetEntity: ArticleTag::class, inversedBy: 'articles')]
    #[ORM\OrderBy(['name' => 'ASC'])]
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

    public function setImage(?File $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->published_at;
    }

    public function setPublishedAt(?\DateTimeInterface $published_at): static
    {
        $this->published_at = $published_at;

        return $this;
    }

    /**
     * @return Collection<int, ArticleTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    // Used in a listener when tags are disabled
    public function clearTags(): static
    {
        $this->tags = new ArrayCollection();

        return $this;
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
        return is_null($this->published_at);
    }

    public function isPublished(): bool
    {
        return !$this->isDraft() && DateTimeUtil::isPast($this->published_at);
    }

    public function isScheduled(): bool
    {
        return !$this->isDraft() && DateTimeUtil::isFuture($this->published_at);
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
