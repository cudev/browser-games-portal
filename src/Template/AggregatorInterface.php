<?php

namespace Ludos\Template;

interface AggregatorInterface
{
    public function getTemplateNames(): array;

    public function hasTemplateName(string $templateName): bool;

    public function getTemplateVariables(): array;
}
