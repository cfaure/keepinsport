<?php

namespace Ks\ActivityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Description of PhotoAlbum
 *
 * @ORM\Entity
 */
class PhotoAlbum extends Activity
{
    
    public function __construct(\Ks\UserBundle\Entity\User $user = null)
    {
        parent::__construct($user);

        $this->type = "photo_album";
    }
}