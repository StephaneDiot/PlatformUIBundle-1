<?php
/**
 * File containing the SectionHelperInterface interface.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\PlatformUIBundle\Helper;

use eZ\Publish\API\Repository\Values\Content\Section;
use EzSystems\PlatformUIBundle\Entity\Section as SectionEntity;

interface SectionHelperInterface
{
    /**
     * Returns the section list as an array. Each element of the returned array
     * is an associated array containing the following entries:
     *      - section: the Section object
     *      - contentCount: the number of contents the section is assigned to
     *      - canEdit: whether the current user can edit the section
     *      - canDelete: whether the current user can delete the section
     *      - canAssign: whether the current user can assign the section to some contents
     *
     * @return array //TODO refactor to return an object ?
     */
    public function getSectionList();

    /**
     * Returns whether the current user can create a new section
     *
     * @return boolean
     */
    public function canCreate();

    /**
     * Returns a section
     * @param int $sectionId
     * @return \eZ\Publish\API\Repository\Values\Content\Section
     */
    public function loadSection( $sectionId );

    /**
     * Returns the number of contents assigned to a given $section
     * @param \eZ\Publish\API\Repository\Values\Content\Section $section
     * @return int
     */
    public function contentCount( Section $section );

    //TODO comment
    public function createSection( SectionEntity $section );

    //TODO comment
    public function updateSection( Section $sectionToUpdate, SectionEntity $section );

}
