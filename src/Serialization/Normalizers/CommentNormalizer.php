<?php

namespace Ludos\Serialization\Normalizers;

use Hashids\Hashids;
use Ludos\Entity\Comment;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CommentNormalizer implements NormalizerInterface
{
    private $urlPackage;
    private $hashids;

    const SAFE_FORMAT = 'safe';

    public function __construct(UrlPackage $urlPackage, Hashids $hashids)
    {
        $this->urlPackage = $urlPackage;
        $this->hashids = $hashids;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if ($format === self::SAFE_FORMAT) {
            /** @var Comment $object */
            $pictureUrl = $object->getUser()->getPictureUrl();
            return [
                'author' => $object->getUser()->getName(),
                'id' => $this->hashids->encode($object->getId()),
                'body' => $object->getBody(),
                'picture' => $pictureUrl ? $this->urlPackage->getUrl($pictureUrl) : null,
                'created' => $object->getCreatedAt()->format('d-m-Y H:i')
            ];
        } else {
            // Currently supports only normalizing comments to public api
            // without any sensitive data
            // TODO: implement
            return null;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Comment;
    }
}
