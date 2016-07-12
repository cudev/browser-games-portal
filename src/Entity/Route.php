<?php

namespace Entity;

use Ludos\Entity\Traits\Identifiable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="routes")
 */
class Route
{
    use Identifiable;
}
