<?php


namespace App\Controller;

use App\Entity\Bookings;
use App\Entity\Customers;
use App\Entity\Payments;
use App\Entity\Rooms;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class BookingController extends AbstractController
{
    /**
     * This method creates Room in the database.
     *
     * @SWG\Tag(
     *     name="Room",
     *     description="Create Rooms"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Room creation successfull",
     *
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Bookings::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Default error state when requested operation has failed",
     *
     *
     * )
     *
     * @SWG\Response(
     *     response=405,
     *     description="Method not allowed",
     *
     *
     * )
     * @IsGranted("ROLE_USER")
     * @Route("/api/booking/create", name="create_booking",methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     * @throws \Exception
     */

    public function createBooking(Request $request, ValidatorInterface $validator): JsonResponse
    {
        if($request->getMethod() != "POST"){
            return new JsonResponse("Only POST method is allowed", 405);
        }

        $body = $request->getContent();


        $data = json_decode($body, true);



        $entityManager = $this->getDoctrine()->getManager();
        $book_time = new \DateTime();


        if(array_key_exists('room_number', $data)) {
            $room_number = $data['room_number'];
        }else{
            return new JsonResponse(['result'=>'failed', 'message'=> 'room number is required'], 400);
        }

        if(array_key_exists('customer_id', $data)) {
            $customer_id = $data['customer_id'];
        }else{
            return new JsonResponse(['result'=>'failed', 'message'=> 'customer id is required'], 400);
        }

        if(array_key_exists('arrival', $data)) {
            $arrival= $data['arrival'];
            $arrival = date('Y-m-d H:i:s', strtotime($arrival));

        }
        if(array_key_exists('checkout', $data)) {
            $checkout = $data['checkout'];
            $checkout = date('Y-m-d H:i:s', strtotime($checkout));
        }

        if(array_key_exists('book_type', $data)) {
            $book_type = $data['book_type'];
        }else{
            return new JsonResponse(['result'=>'failed', 'message'=> 'book type is required'], 400);
        }

        if(array_key_exists('payment', $data)) {
            $payAmount = $data['payment'];
        }else{
            $payAmount = 0;
        }

        $room = $entityManager->getRepository(Rooms::class)->findOneBy(['room_number'=> $room_number]);

        if(!$room){

            return new JsonResponse(["result"=> "failed","message" => "room id not found or invalid type supplied"]);
        }

        $customer = $entityManager->getRepository(Customers::class)->findBy(['id'=> $customer_id]);

        if(!$customer){
            return new JsonResponse(["result"=> "failed","message" => "customer id not found or invalid type supplied"]);
        }

        if (!empty($arrival) AND !empty($checkout)){
            if($arrival > $checkout){
                return new JsonResponse(["result"=> "failed", "message"=>"arrival can not be later than checkout"]);
            }
        }
        if(!in_array($book_type,['pending','partial','confirmed'])){

            return new JsonResponse(["result"=> "failed","message" => "booking type must be one of these pending, partial, confirmed"]);
        }

        $isBooked = $entityManager->getRepository(Bookings::class)->findByFreeRoomBycheckinAndCheckout($room->getId(),$arrival, $checkout);
        if($isBooked){

            return new JsonResponse(['result'=> 'failed', 'message' => 'room is not free given arival and checkout time']);
        }

        try {
            $book = new Bookings();
            $book->setCustomerId($customer_id);
            $book->addRoomBooking($room);
            $book->setBookTime($book_time);
            $book->setBookType($book_type);
            if (!empty($arrival)) {
                $book->setArrival(new \DateTime($arrival));
            }
            if (!empty($checkout)) {
                $book->setCheckout(new \DateTime($checkout));
            }

            $entityManager->persist($book);

            $entityManager->flush();
            $payment  = new Payments();
            $payment->setCustomerId($customer_id);
            $payment->setBookingId($book->getId());
            $payment->setAmount($payAmount);
            $payment->setDate(new \DateTime());
            $entityManager->persist($payment);
            $entityManager->flush();

            return new JsonResponse(['result' => "success", "message" => 'room successfully booked']);
        } catch (\Exception $e) {

            return new JsonResponse(['result' => "failed", "message" => $e->getMessage()]);
        }

    }

    /**
     * @param Request $request
     * @SWG\Response(
     *     response=200,
     *     description="Operation successfull",
     *
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Bookings::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Default error state when requested operation has failed",
     *
     *
     * )
     *
     * @SWG\Response(
     *     response=405,
     *     description="Method not allowed",
     *
     *
     * )
     *
     * @return JsonResponse
     * @IsGranted("ROLE_USER")
     * @Route("/api/booking/checkout", name="checkout",methods={"POST"})
     */
    public function checkout(Request $request): JsonResponse
    {
        if($request->getMethod() != "POST"){
            return new JsonResponse("Only POST method is allowed", 405);
        }

        $body = $request->getContent();
        $data = json_decode($body, true);
        $entityManager = $this->getDoctrine()->getManager();

        if(array_key_exists('checkout', $data)) {
            $checkout = $data['checkout'];
            $checkout = date('Y-m-d H:i:s', strtotime($checkout));
        }else{
            return new JsonResponse(['result'=>'failed', 'message'=> 'checkout is required'], 400);
        }

        if(array_key_exists('customer_id', $data)) {
            $customer_id = $data['customer_id'];
        }else{
            return new JsonResponse(['result'=>'failed', 'message'=> 'customer id is required'], 400);
        }

        if(array_key_exists('booking_id', $data)) {
            $booking_id = $data['booking_id'];
        }else{
            return new JsonResponse(['result'=>'failed', 'message'=> 'booking_id is required'], 400);
        }

        $customer = $entityManager->getRepository(Customers::class)->findBy(['id'=> $customer_id]);

        if(!$customer){
            return new JsonResponse(["result"=> "failed","message" => "customer id not found or invalid type supplied"]);
        }

        /** @var  $booking Bookings */
        $booking = $entityManager->getRepository(Bookings::class)->findOneBy(['id'=> $booking_id]);
        if(!$booking){
            return new JsonResponse(["result"=> "failed","message" => "booking id not found or invalid type supplied"]);
        }

        /** @var  $payments Payments */
        $payments = $entityManager->getRepository(Payments::class)->findOneBy(['booking_id'=> $booking_id]);
        $paid= $payments->getAmount();
        /** @var  $room_data Rooms */
        $room_data = $booking->getRoomBooking();
        foreach ($room_data as $room){
            $room_rate = $room->getPrice();
        }
        $due = ($room_rate - $paid);

        if($due > 0){
            if(array_key_exists('payment', $data)) {
                $paying_amount = $data['payment'];
                if($due != $paying_amount){
                    return new JsonResponse(['result'=>'failed', 'message'=> 'due is greater that paying amount'], 400);
                }
            }else{
                return new JsonResponse(['result'=>'failed', 'message'=> 'Payment is due, hence payment is required'], 400);
            }
        }
        $payments->setAmount($paying_amount);
        $booking->setBookType('complete');

        // updating booking information
        $entityManager->persist($payments);
        $entityManager->persist($booking);
        $entityManager->flush();

        return new JsonResponse(['result'=> 'success', 'message'=> "checkout successfully"]);

    }

    /**
     * @SWG\Response(
     *     response=200,
     *     description="Operation successfull",
     *
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Bookings::class))
     *     )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Default error state when requested operation has failed",
     *
     *
     * )
     *
     * @SWG\Response(
     *     response=405,
     *     description="Method not allowed",
     *
     *
     * )
     * @param Request $request
     * @param EntityManagerInterface $em
     *
     * @return JsonResponse
     *
     * @IsGranted("ROLE_USER")
     * @Route("/api/booking/list", name="list_booking",methods={"GET"})
     */

    public function listBookings(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $bookings = $em->getRepository(Bookings::class)->findBookingDetails();

        return new JsonResponse($bookings, 200);
    }

    

}