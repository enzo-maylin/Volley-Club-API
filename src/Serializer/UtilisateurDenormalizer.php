<?php

namespace App\Serializer;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Utilisateur;
use App\Entity\Equipe;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
class UtilisateurDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    //On injecte le service qui permet de convertir un id en IRI.
    public function __construct(private IriConverterInterface $iriConverter)
    {}

    //On indique dans cette méthode quel sont les objets gérés par notre denormalizer
    public function getSupportedTypes(?string $format): array
    {
        return [
            //Paramètres obligatoires
            'object' => null,
            '*' => false,
            //On indique que cette classe permet seulement de gérer la dénormalisation d'un utilisateur
            Utilisateur::class => true
        ];
    }
    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return \in_array($format, ['json', 'jsonld'], true) &&
            is_a($type, Utilisateur::class, true) &&
            !empty($data['equipe']) &&
            !isset($context[__CLASS__]);
    }

    //Cette méthode convertit l'identifiant simple en IRI puis reprend le processus normal de dénormalisation
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        //Convertir le champ $data["ville"] contenant un id simple en IRI
        //On indique bien quelle est l'entité visée (ici, Ville)
        $data['equipe'] = $this->iriConverter->getIriFromResource(resource: Equipe::class, context: ['uri_variables' => ['id' => $data['equipe']]]);

        //On reprend le processus de dénormalisation
        return $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);
    }
}