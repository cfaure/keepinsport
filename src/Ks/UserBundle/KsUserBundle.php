<?php

namespace Ks\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class KsUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}