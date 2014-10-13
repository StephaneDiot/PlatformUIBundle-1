<?php
/**
 * File containing the SectionType class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace EzSystems\PlatformUIBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Section
 *
 * Section Entity to use with Symfony's form component
 *
 * @package EzSystems\PlatformUIBundle\Entity
 */
class Section
{
    //TODO validation (messages)
    /**
     * @Assert\NotBlank( message ="section.validate.identifier.not_blank" )
     * @Assert\Regex(
     *    pattern="/(^[^A-Za-z])|\W/",
     *    match=false,
     *    message="section.validate.identifier.format"
     * )
     */
    public $identifier;

    //TODO validation
    /**
     * @Assert\NotBlank()
     */
    public $name;
}
