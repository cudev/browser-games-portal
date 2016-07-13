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

namespace Ludos\Action;

use Doctrine\ORM\EntityManager;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use Ludos\Asset\HashedPackage;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Zend\Diactoros\Response\JsonResponse;

class UpdateUserPicture
{
    private $entityManager;
    private $filesystem;
    private $hashedPackage;
    private $deletePictureAction;

    const PICTURE_SIZE_LIMIT = 2 * 1024 * 1024; // 2Mb

    public function __construct(
        EntityManager $entityManager,
        Filesystem $filesystem,
        HashedPackage $hashedPackage,
        DeleteUserPicture $deleteUserPictureAction
    ) {
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->hashedPackage = $hashedPackage;
        $this->deletePictureAction = $deleteUserPictureAction;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $data = [];

        // get only first uploaded file
        /** @var UploadedFileInterface $picture */
        $uploadedFiles = $request->getUploadedFiles();
        $picture = reset($uploadedFiles);

        if ($picture->getSize() > self::PICTURE_SIZE_LIMIT) {
            return new JsonResponse(['success' => false], 413);
        }

        if ($picture !== false && $picture->getError() === 0) {
            $deleteAction = $this->deletePictureAction;
            $deleteAction($request, $response);
            $pictureExtension = image_type_to_extension(exif_imagetype($picture->getStream()->getMetadata('uri')));
            $name = md5_file($picture->getStream()->getMetadata('uri')) . $pictureExtension;
            try {
                $this->filesystem->writeStream(
                    HashedPackage::getSubdirectories($name) . $name,
                    $picture->getStream()->detach()
                );
            } catch (FileExistsException $exception) {
                // it's okay
            }
            $user->setPicture($name);
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $data['picture'] = $this->hashedPackage->getUrl($user->getPicture());
        }

        return new JsonResponse(['success' => true, 'data' => $data]);
    }
}
