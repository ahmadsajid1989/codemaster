<?php


namespace App\Controller;

use App\Entity\Bookings;
use App\Entity\Customers;
use App\Entity\Payments;
use App\Entity\Rooms;
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

        $booking = $entityManager->getRepository(Bookings::class)->findByFreeRoomField($room_number,$checkout);





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

}