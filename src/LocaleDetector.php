<?php

namespace Ludos;

use Ludos\Entity\Locale;
use Ludos\Entity\Repositories\LocaleRepository;
use Psr\Http\Message\ServerRequestInterface;

class LocaleDetector
{
    private $localeRepository;
    private $request;

    public function __construct(LocaleRepository $localeRepository, ServerRequestInterface $request)
    {
        $this->localeRepository = $localeRepository;
        $this->request = $request;
    }

    public function detect(): Locale
    {
        $criteria = [
            'domain' => $this->request->getServerParams()['SERVER_NAME']
        ];

        return $this->localeRepository->findOneBy($criteria) ?? $this->localeRepository->getDefault();
    }
}
