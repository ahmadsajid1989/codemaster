<?php


namespace App\Controller;

use App\Entity\Customers;
use App\Form\CustomerType;

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

/**
 * Class CustomerController
 * @package App\Controller
 * @IsGranted("ROLE_USER")
 */
class CustomerController extends AbstractController
{
    /**
     * This method creates customers in the database.
     *
     * @SWG\Tag(
     *     name="Customers",
     *     description="Create Customers"
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Customer creation successfull",
     *
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=Customers::class))
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
     * @Route("/api/customer/", name="create_customer",methods={"POST"})
     * @param Request $request
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */

    public function createCustomer(Request $request, ValidatorInterface $validator): JsonResponse
    {
        if($request->getMethod() != "POST"){
            return new JsonResponse("Only POST method is allowed", 405);
        }

        $body = $request->getContent();
        $data = json_decode($body, true);

        $customer = new Customers();
        $form = $this->createForm(CustomerType::class, $customer);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {

            $customer->setRegisteredAt(new \DateTime());

            try {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($customer);
                $entityManager->flush();
                return new JsonResponse(["result" => "success", "message" =>"Customer Created Successfully"], 200);
            } catch (\Exception $e) {
                return new JsonResponse(array("result" => "failed", "message" => $e->getMessage()), 400);
            }
        }


        $violations = $validator->validate($customer);

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