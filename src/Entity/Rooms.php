<?php

namespace App\Entity;

use App\Repository\RoomsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass=RoomsRepository::class)
 * @UniqueEntity("room_number")
 */
class Rooms
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @SWG\Property(description="The unique identifier of the room.")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", unique=true)
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @SWG\Property(type="integer")
     * @Groups({"list"})
     */
    private $room_number;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="float",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @SWG\Property(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="boolean")
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="bool",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @SWG\Property(type="boolean")
     */
    private $locked;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(
     *      min = 1,
     *      max = 1,
     *      minMessage = "must be at least {{ limit }} characters long",
     *      maxMessage = "can not accept more that {{ limit }}")
     * @SWG\Property(type="integer")
     */
    private $max_person;

    /**
     * @Assert\Type(
     *     type="string",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @ORM\Column(type="string", length=255)
     */
    private $room_type;

    /**
     * @var \Doctrine\Common\Collections\Collection|User[]
     *
     * @ORM\ManyToMany(targetEntity="Bookings", mappedBy="roomBookings")
     */
    protected $bookings;

    /**
     * Default constructor, initializes collections
     */
    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoomNumber(): ?int
    {
        return $this->room_number;
    }

    public function setRoomNumber(int $room_number): self
    {
        $this->room_number = $room_number;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getLocked(): ?bool
    {
        return $this->locked;
    }

    public function setLocked(bool $locked): self
    {
        $this->locked = $locked;

        return $this;
    }

    public function getMaxPerson(): ?int
    {
        return $this->max_person;
    }

    public function setMaxPerson(int $max_person): self
    {
        $this->max_person = $max_person;

        return $this;
    }

    public function getRoomType(): ?string
    {
        return $this->room_type;
    }

    public function setRoomType(string $room_type): self
    {
        $this->room_type = $room_type;

        return $this;
    }
}
