<?php

namespace App\Entity;

use App\Repository\BookingsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass=BookingsRepository::class)
 */
class Bookings
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @SWG\Property(description="The unique identifier of the room.")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"list"})
     * @Assert\Type(
     *     type="datetime",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @SWG\Property(type="datetime")
     */
    private $arrival;

    /**
     * @ORM\Column(type="datetime")
     */
    private $checkout;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $book_type;

    /**
     * @ORM\Column(type="datetime")
     */
    private $book_time;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $customer_id;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Rooms", inversedBy="bookings")
     * @ORM\JoinTable(
     *  name="room_bookings",
     *  joinColumns={
     *      @ORM\JoinColumn(name="booking_id", referencedColumnName="id")
     *  },
     *  inverseJoinColumns={
     *      @ORM\JoinColumn(name="room_id", referencedColumnName="id")
     *  }
     * )
     */

    private $roomBooking;

    public function __construct()
    {
        $this->roomBooking = new ArrayCollection();
    }

    public function addRoomBooking(Rooms $room){
        if ($this->roomBooking->contains($room)) {
            return;
        }

        $this->roomBooking->add($room);
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArrival(): ?\DateTimeInterface
    {
        return $this->arrival;
    }

    public function setArrival(\DateTimeInterface $arrival): self
    {
        $this->arrival = $arrival;

        return $this;
    }

    public function getCheckout(): ?\DateTimeInterface
    {
        return $this->checkout;
    }

    public function setCheckout(\DateTimeInterface $checkout): self
    {
        $this->checkout = $checkout;

        return $this;
    }

    public function getBookType(): ?string
    {
        return $this->book_type;
    }

    public function setBookType(?string $book_type): self
    {
        $this->book_type = $book_type;

        return $this;
    }

    public function getBookTime(): ?\DateTimeInterface
    {
        return $this->book_time;
    }

    public function setBookTime(\DateTimeInterface $book_time): self
    {
        $this->book_time = $book_time;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @param mixed $customer_id
     */
    public function setCustomerId($customer_id): void
    {
        $this->customer_id = $customer_id;
    }


}
