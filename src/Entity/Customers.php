<?php

namespace App\Entity;

use App\Repository\CustomersRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass=CustomersRepository::class)
 * @UniqueEntity("phone")
 * @UniqueEntity("email")
 */
class Customers
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @SWG\Property(description="The unique identifier of the user.")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @SWG\Property(type="string", maxLength=180)
     * @Groups({"list"})
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\NotBlank()
     * @SWG\Property(type="string", maxLength=180)
     * @Groups({"list"})
     */
    private $last_name;

    /**
     * @ORM\Column(type="string", length=180,unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @SWG\Property(type="string", maxLength=180)
     * @Groups({"list"})
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20,unique=true)
     * @Assert\NotBlank()
     * @Assert\Length(
     *      min = 11,
     *      max = 11,
     *      minMessage = "Your phone number must be at least {{ limit }} characters long and must be started with 0",
     *      maxMessage = "Your first name cannot be longer than {{ limit }} characters")
     * @SWG\Property(type="string", maxLength=180)
     * @Groups({"list"})
     */
    private $phone;

    /**
     * @ORM\Column(type="datetime")
     */
    private $registered_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getRegisteredAt(): ?\DateTimeInterface
    {
        return $this->registered_at;
    }

    public function setRegisteredAt(\DateTimeInterface $registered_at): self
    {
        $this->registered_at = $registered_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }
}
