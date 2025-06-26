<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $sender = null;

    #[ORM\ManyToOne(inversedBy: 'rmessages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $recipient = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $modify_at = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isResponse = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isRead = null;

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'message')]
    private Collection $files;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastSender = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isFavorite = null;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): static
    {
        $this->subject = $subject;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;

        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(?User $recipient): static
    {
        $this->recipient = $recipient;

        return $this;
    }

    public function getModifyAt(): ?\DateTimeImmutable
    {
        return $this->modify_at;
    }

    public function setModifyAt(?\DateTimeImmutable $modify_at): static
    {
        $this->modify_at = $modify_at;

        return $this;
    }

    public function isResponse(): ?bool
    {
        return $this->isResponse;
    }

    public function setIsResponse(?bool $isResponse): static
    {
        $this->isResponse = $isResponse;

        return $this;
    }

    public function isRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): static
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setMessage($this);
        }

        return $this;
    }

    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getMessage() === $this) {
                $file->setMessage(null);
            }
        }

        return $this;
    }

    public function getLastSender(): ?string
    {
        return $this->lastSender;
    }

    public function setLastSender(?string $lastSender): static
    {
        $this->lastSender = $lastSender;

        return $this;
    }

    // src/Entity/Message.php

    public function isUserAllowed(User $user): bool
    {
        // Par exemple, l’utilisateur est autorisé s’il est l’expéditeur ou le destinataire
        return $this->getSender() === $user || $this->getRecipient() === $user;
    }

    public function isFavorite(): ?bool
    {
        return $this->isFavorite;
    }

    public function setIsFavorite(?bool $isFavorite): static
    {
        $this->isFavorite = $isFavorite;

        return $this;
    }


}