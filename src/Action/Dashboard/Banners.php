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

namespace Ludos\Action\Dashboard;

use Doctrine\ORM\EntityManager;
use League\Flysystem\FileExistsException;
use League\Flysystem\Filesystem;
use Ludos\Asset\HashedPackage;
use Ludos\Entity\Banner;
use Ludos\Entity\Game\Game;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class Banners extends AbstractResourceAction
{
    protected $entityManager;
    protected $serializer;
    protected $filesystem;
    private $hashedPackage;

    public function __construct(
        EntityManager $entityManager,
        Serializer $serializer,
        Filesystem $filesystem,
        HashedPackage $hashedPackage
    ) {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->filesystem = $filesystem;
        $this->hashedPackage = $hashedPackage;
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $id = $request->getAttribute('bannerId');
        if (is_numeric($id)) {
            $banner = $this->entityManager->getRepository(Banner::class)->find($id);
            if ($banner === null) {
                return $next($request, $response->withStatus(404), 'Not found');
            }
            $banners = [$banner];
        } elseif ($id === null) {
            $banners = $this->entityManager->getRepository(Banner::class)->findAll();
        } else {
            $banners = [
                new Banner()
            ];
        }

        $normalizedBanners = [];
        foreach ($banners as $banner) {
            /** @var array $normalizedBanner */
            $normalizedBanner = $this->serializer->normalize($banner);

            $normalizedBanner['pictureUri'] = '';
            if ($normalizedBanner['picture']) {
                $normalizedBanner['pictureUri'] = $this->hashedPackage->getUrl($normalizedBanner['picture']);
            }
            $normalizedBanner['gameId'] = $normalizedBanner['game']['id'];
            $normalizedBanners[] = $normalizedBanner;
        }

        $normalizedGames = [];

        if ($id !== null) {
            /** @var Game[] $games */
            $games = $this->entityManager->getRepository(Game::class)->findAll();

            foreach ($games as $game) {
                $normalizedGames[] = [
                    'id' => $game->getId(),
                    'name' => $game->getName()
                ];
            }
        }

        return new JsonResponse([
            'data' => $id !== null ? $normalizedBanners[0] : $normalizedBanners,
            'included' => ['games' => $normalizedGames]
        ]);
    }

    public function post(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $errors = [];

        $updatedBanner = $this->serializer->decode($request->getBody()->getContents(), 'json');
        $gameId = $updatedBanner['gameId'] ?? null;

        $pictureDataUri = $updatedBanner['newPicture'] ?? null;
        if ($pictureDataUri !== null) {
            $pictureName = $this->getPictureNameFromDataUri($pictureDataUri);
            $pictureData = $this->getDataFromDataUri($pictureDataUri);
            // Get path with directories
            $picturePath = HashedPackage::getSubdirectories($pictureName) . $pictureName;
            try {
                $this->filesystem->write($picturePath, $pictureData);
            } catch (FileExistsException $exception) {
                // It's okay if file exists, benefits from md5 naming
            }

            if (isset($updatedBanner['picture'])) {
                $this->filesystem->delete(
                    HashedPackage::getSubdirectories($updatedBanner['picture']) . $updatedBanner['picture']
                );
            }
            $updatedBanner['picture'] = $pictureName;
            $updatedBanner['newPicture'] = '';
        }

        $game = null;
        if ($gameId !== null) {
            $game = $this->entityManager->getRepository(Game::class)->find($gameId);
            if ($game === null) {
                $errors[] = 'Game is not selected';
            }
            /** @var Banner $banner */
            $banner = $this->serializer->denormalize($updatedBanner, Banner::class);
            $banner->setGame($game);
            $this->entityManager->persist($banner);
            $this->entityManager->flush();
        }

        /** @var array $updatedBanner */
        $updatedBanner = $this->serializer->normalize($banner);

        $updatedBanner['gameId'] = $gameId;
        $updatedBanner['pictureUri'] = '';
        if ($updatedBanner['picture']) {
            $updatedBanner['pictureUri'] = $this->hashedPackage->getUrl($updatedBanner['picture']);
        }

        return new JsonResponse([
            'error' => implode('. ', $errors),
//            'included' => ['games' => $normalizedGames],
            'data' => $updatedBanner
        ]);
    }

    private function getDataFromDataUri(string $dataUri)
    {
        // String has "data:[<mediatype>][;base64],<data>" format
        // extracting the actual <data> part here
        return base64_decode(substr($dataUri, strpos($dataUri, ',') + 1));
    }

    private function getPictureNameFromDataUri(string $dataUri)
    {
        $pictureData = $this->getDataFromDataUri($dataUri);
        // Get picture extension from mime-type and construct file name
        $matches = [];
        preg_match('/^data:image\/(\w+);/', $dataUri, $matches);
        $extension = $matches[1];
        return md5($pictureData) . '.' . $extension;
    }

    public function patch(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->post($request, $response);
    }

    public function delete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $id = $request->getAttribute('bannerId');
        /** @var Banner|null $banner */
        $banner = null;
        if ($id !== null) {
            $banner = $this->entityManager->getRepository(Banner::class)->find($id);
        }
        if ($banner === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }


        $picturePath = $banner->hasPicture()
            ? HashedPackage::getSubdirectories($banner->getPicture()) . '/' . $banner->getPicture()
            : null;

        if ($picturePath !== null && $this->filesystem->has($picturePath)) {
            $this->filesystem->delete($picturePath);
        }
        $this->entityManager->remove($banner);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}
