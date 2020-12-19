<?php


namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;


class AuthController extends AbstractController
{
    /**
     * @Route("/auth/register", name="user_registration",methods={"POST"})
     * @SWG\Response(
     *     response=200,
     *     description="Creates an user",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=User::class, groups={"write"}))
     *     )
     * )
     * @SWG\Tag(name="users")
     *
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @param ValidatorInterface $validator
     *
     * @return JsonResponse
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder,ValidatorInterface $validator): JsonResponse
    {
        if($request->getMethod() != "POST"){
            return new JsonResponse("Only POST method is allowed", 405);
        }

        $body = $request->getContent();
        $data = json_decode($body, true);


        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            # to avoid complexity we are enabling the user on registration. later we can
            #have a system to enable user upon verification.
            $user->setStatus(true);

            // 4) save the User!

            try {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                return new JsonResponse(["result" => "success", "message" =>"User Created Successfully"], 200);
            } catch (\Exception $e) {
                return new JsonResponse(array("result" => "failed", "message" => $e->getMessage()), 400);
            }
        }

        $violations = $validator->validate($user);

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