<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\InscriptionEvenement;
use App\Repository\EquipeRepository;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InscriptionEvenementPutProcessor implements ProcessorInterface
{

    //Injection des repositories et de du service EntityManager
    public function __construct(
        private EquipeRepository $equipeRepository,
        private EvenementRepository $evenementRepository,
        private EntityManagerInterface $entityManager,
        private Security $security,
    )
    {}

    //$data est un objet Inscription fourni par le StateProvider.
    //Dans ce contexte (PUT avec allowCreate: true), il peut être null si le evenement n'était pas déjà inscrit à l'évènement en question.
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        //Si l'inscription n'existe pas déjà (null retourné par le StateProvider)
        if(!$data) {
            //$uriVariables contient les valeurs des variables fournies au travers de l'URI de la route
            $idEquipe = $uriVariables["idEquipe"];
            $equipe = $this->equipeRepository->find($idEquipe);
            if(!$equipe) {
                throw new NotFoundHttpException("Equipe inexistant.");
            }

            if ($equipe->getClub()->getCoach() !== $this->security->getUser()) {
                throw new AccessDeniedHttpException("Vous n'êtes pas autorisé à modifier cette équipe.");
            }

            $idEvenement = $uriVariables["idEvenement"];
            $evenement = $this->evenementRepository->find($idEvenement);
            if(!$evenement) {
                throw new NotFoundHttpException("Evenement inexistant.");
            }

            // Vérification si l'événement est déjà complet
            if ($evenement->isComplet()) {
                throw new BadRequestHttpException("L'événement est déjà complet, inscription impossible.");
            }

            //On créé l'objet InscriptionEvement à retourner au client
            $data = new InscriptionEvenement();
            $data->setEquipe($equipe);
            $data->setEvenement($evenement);

            //On ajoute l'évènement à la collection d'évènements d'une équipe
            if($equipe->getEvenements()->contains($evenement)) {
                throw new BadRequestHttpException("Cette équipe est déjà inscrit à un evenement");
            }

            // Vérifier les conflits de dates
            foreach ($equipe->getEvenements() as $inscription) {
                // Vérifiez si les dates se chevauchent
                if ($this->datesSeChevauchent($inscription, $evenement)) {
                    throw new BadRequestHttpException("Cette équipe ne peut pas être inscrite à deux événements en même temps.");
                }
            }

            $equipe->addEvenement($evenement);

            //On sauvegarde les changements
            $this->entityManager->flush();
        }
        return $data;
    }

    private function datesSeChevauchent($evenement1, $evenement2): bool
    {
        // Récupérez les dates de début et de fin de chaque événement
        $debut1 = $evenement1->getDateDebut(); // Supposez que cette méthode retourne la date de début
        $fin1 = $evenement1->getDateFin(); // Supposez que cette méthode retourne la date de fin
        $debut2 = $evenement2->getDateDebut();
        $fin2 = $evenement2->getDateFin();

        // Vérifiez si les événements se chevauchent
        return ($debut1 < $fin2) && ($debut2 < $fin1);
    }

}
