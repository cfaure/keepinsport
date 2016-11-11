<?php

namespace Ks\ClubBundle\Namer;

use Vich\UploaderBundle\Naming\NamerInterface;

/**
 * Namer class.
 */
class Namer implements NamerInterface
{
    /**
     * Creates a name for the file being uploaded.
     *
     * @param object $obj The object the upload is attached to.
     * @param string $field The name of the uploadable field to generate a name for.
     * @return string The file name.
     */
    function name($obj, $field)
    {
        $file = $obj->getImage();
        $extension = $file->guessExtension();

        return uniqid('img_').'.'.$extension;
    }
    
}
