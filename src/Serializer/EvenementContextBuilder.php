<?php

namespace App\Serializer;


use ApiPlatform\State\SerializerContextBuilderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use App\Entity\Evenement;
class EvenementContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;
    private $authorizationChecker;
    private $entityManager;

    public function __construct(SerializerContextBuilderInterface $decorated, EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
        $this->entityManager = $entityManager;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
       $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        $data = json_decode($request->getContent(), true);

        $id = $request->attributes->get('id');

        if ($resourceClass === Evenement::class && true === $normalization) {
            if( isset($data['public']) && $data['public'] === true){
                $context['groups'][] = 'public:read';
            }

            if ($id) {
                $evenement = $this->entityManager->getRepository(Evenement::class)->find($id);
                if ($evenement && $evenement->isPublic()) {
                    $context['groups'][] = 'public:read';
                }
            }
        }

        return $context;
    }
}