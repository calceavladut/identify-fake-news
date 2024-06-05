<?php

namespace App\Entity;

use App\Repository\ExtractedArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExtractedArticleRepository::class)]
class ExtractedArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    /**
     * @Assert\Url(
     *    protocols = {"http", "https", "ftp"},
     *    message = "The url is not a valid url. Example for a valid url: ' https://www.your-article.ro/ '",
     * )
     */
    private ?string $url = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $text = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $original_title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $original_content = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $translated_title = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $translated_content = null;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $real_score = 0.00;

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $fake_score = 0.00;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }


    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): ExtractedArticle
    {
        $this->text = $text;

        return $this;
    }

    public function getOriginalTitle(): ?string
    {
        return $this->original_title;
    }

    public function setOriginalTitle(string $original_title): self
    {
        $this->original_title = $original_title;

        return $this;
    }

    public function getOriginalContent(): ?string
    {
        return $this->original_content;
    }

    public function setOriginalContent(string $original_content): self
    {
        $this->original_content = $original_content;

        return $this;
    }

    public function getTranslatedTitle(): ?string
    {
        return $this->translated_title;
    }

    public function setTranslatedTitle(?string $translated_title): self
    {
        $this->translated_title = $translated_title;

        return $this;
    }

    public function getTranslatedContent(): ?string
    {
        return $this->translated_content;
    }

    public function setTranslatedContent(?string $translated_content): self
    {
        $this->translated_content = $translated_content;

        return $this;
    }

    public function getRealScore(): ?float
    {
        return $this->real_score;
    }

    public function setRealScore(?float $real_score): self
    {
        $this->real_score = $real_score;

        return $this;
    }

    public function getFakeScore(): ?float
    {
        return $this->fake_score;
    }

    public function setFakeScore(?float $fake_score): self
    {
        $this->fake_score = $fake_score;

        return $this;
    }
}
