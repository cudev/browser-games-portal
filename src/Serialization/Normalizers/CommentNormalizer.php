<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
