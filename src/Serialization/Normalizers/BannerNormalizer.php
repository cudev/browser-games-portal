<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Banner;
use Ludos\Entity\BannerTitle;
use Ludos\Entity\Game\Game;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Traversable;

class BannerNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Banner $object */
        $bannerTitles = $object->getBannerTitles() ?? [];
        return [
            'id' => $object->getId(),
            'enabled' => $object->isEnabled(),
            'game' => $this->serializer->normalize($object->getGame(), $format, $context),
            'picture' => $object->getPicture(),
            'priority' => $object->getPriority(),
            'bannerTitles' => array_reduce(
                $bannerTitles instanceof Traversable ? iterator_to_array($bannerTitles) : $bannerTitles,
                function ($result, BannerTitle $bannerTitle) use ($format, $context) {
                    $result[$bannerTitle->getLocale()->getLanguage()] = $this->serializer->normalize(
                        $bannerTitle,
                        $format,
                        $context
                    );
                    return $result;
                }
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Banner;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $bannerTitles = $data['bannerTitles'] ?? [];
        /** @var Banner $banner */
        $banner = $this->fetchEntity($data, Banner::class);
        $banner->setEnabled((boolean)$data['enabled'])
            ->setPicture($data['picture'])
            ->setPriority($data['priority']);
        if ($data['game']) {
            $banner->setGame($this->serializer->denormalize($data['game'], Game::class, $format, $context));
        }
        foreach ($bannerTitles as $bannerTitle) {
            $banner->addBannerTitle(
                $this->serializer->denormalize($bannerTitle, BannerTitle::class, $format, $context)
            );
        }
        return $banner;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Banner::class;
    }
}
