<?php

namespace Ludos\Serialization\Normalizers;

use Carbon\Carbon;
use Ludos\Entity\User;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var User $object */
        $birthday = $object->getBirthday();
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'email' => $object->getEmail(),
            'birthday' => $birthday ? $this->serializer->normalize($birthday, $format, $context) : null,
            'gender' => $object->getGender(),
            'pictureUrl' => $object->getPictureUrl()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof User;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $user = $this->fetchEntity($data, User::class);
        /** @var User $user */
        $user->setName($data['name'])
            ->setEmail($data['email'])
            ->setBirthday(
                $data['birthday']
                    ? $this->serializer->denormalize($data['birthday'], Carbon::class, $format, $context)
                    : null
            )
            ->setGender($data['gender']);
        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === User::class;
    }
}
