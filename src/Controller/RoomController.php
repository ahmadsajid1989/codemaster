<?php


namespace App\Controller;

use App\Entity\Rooms;
use App\Form\RoomType;
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

class RoomController extends AbstractController
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
     *         @SWG\Items(ref=@Model(type=Rooms::class))
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
     * @Route("/api/rooms/", name="create_room",methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */
    public function createRoom(Request $request, ValidatorInterface $validator): JsonResponse
    {
        if($request->getMethod() != "POST"){
            return new JsonResponse("Only POST method is allowed", 405);
        }

        $body = $request->getContent();
        $data = json_decode($body, true);
        $entityManager = $this->getDoctrine()->getManager();
        $room_number = $data['room_number'];

        $room = $entityManager->getRepository(Rooms::class)->findBy(['room_number'=> $room_number]);

        if($room){
            return new JsonResponse(["result" => "failed", "message" => "This room number exists"], 400);
        }

        $room = new Rooms();
        $form = $this->createForm(RoomType::class, $room);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $room->setLocked(false);

            try {

                $entityManager->persist($room);
                $entityManager->flush();
                return new JsonResponse(["result" => "success", "message" =>"Room Created Successfully"], 200);
            } catch (\Exception $e) {
                return new JsonResponse(array("result" => "failed", "message" => $e->getMessage()), 400);
            }
        }

        $violations = $validator->validate($room);

        if(count($violations) > 0) {

            /** @var  $error ConstraintViolation */
            foreach ($violations as $violation) {
                $errors[] = [$violation->getPropertyPath() => $violation->getMessage()];
            }
        }


        $form_errors = $form->getErrors();

        if (count($form_errors) > 0) {
            /** @var  $form_error FormError */
            foreach ($form_errors as $form_error) {
                $errors[] =  $form_error->getMessage();
            }
        }

        return new JsonResponse(array("result" => "failed", "message" => $errors), 400);
    }

}