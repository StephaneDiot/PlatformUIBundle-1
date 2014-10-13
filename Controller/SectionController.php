<?php
/**
 * File containing the SectionController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\PlatformUIBundle\Controller;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use EzSystems\PlatformUIBundle\Entity\Section;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EzSystems\PlatformUIBundle\Helper\SectionHelperInterface;

class SectionController extends PjaxController
{
    /**
     * @var \EzSystems\PlatformUIBundle\Helper\SectionHelperInterface
     */
    protected $sectionHelper;

    public function __construct( SectionHelperInterface $sectionHelper )
    {
        $this->sectionHelper = $sectionHelper;
    }

    /**
     * Renders the section list
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $response = new Response();
        try
        {
            return $this->render(
                'eZPlatformUIBundle:Section:list.html.twig',
                array(
                    'sectionInfoList' => $this->sectionHelper->getSectionList(),
                    'canCreate' => $this->sectionHelper->canCreate(),
                ),
                $response
            );
        }
        catch ( UnauthorizedException $e )
        {
            $response->setStatusCode( $this->getNoAccessStatusCode() );
        }
        return $response;
    }

    /**
     * Renders the view of a section
     * @param int $sectionId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction( $sectionId )
    {
        $response = new Response();
        try
        {
            $section = $this->sectionHelper->loadSection( $sectionId );
            $contentCount = $this->sectionHelper->contentCount( $section );
            return $this->render(
                "eZPlatformUIBundle:Section:view.html.twig",
                array(
                    'section' => $section,
                    'contentCount' => $contentCount,
                ),
                $response
            );
        }
        catch ( UnauthorizedException $e )
        {
            $response->setStatusCode( $this->getNoAccessStatusCode() );
        }
        return $response;
    }

    //TODO comment
    public function createAction( Request $request)
    {
        $section = new Section();

        $form = $this->createForm(
            $this->get( 'ezsystems.platformui.form.type.section' ),
            $section,
            array(
                //TODO ?
                'action' => $this->get( 'router' )->generate( 'admin_sectioncreate' ),
            )
        );

        $form->handleRequest( $request );

        if ( $form->isValid() )
        {
            $this->sectionHelper->createSection( $section );

            return $this->render(
                'eZPlatformUIBundle:Section:list.html.twig',
                array(
                    'sectionInfoList' => $this->sectionHelper->getSectionList(),
                    'canCreate' => $this->sectionHelper->canCreate(),
                )
            );
        }
        else
        {
            //TODO check if can create ?
            //TODO redirect and success message

            return $this->render(
                'eZPlatformUIBundle:Section:create.html.twig',
                array(
                    'form' => $form->createView(),
                )
            );
        }
    }

    //TODO comment (check if throw clause)
    public function editAction( Request $request, $sectionId )
    {
        $sectionToUpdate = $this->sectionHelper->loadSection( $sectionId );

        //TODO section doesn't exist --> error

        // Loading API data
        $section = new Section();
        $section->identifier = $sectionToUpdate->identifier;
        $section->name = $sectionToUpdate->name;

        $form = $this->createForm(
            $this->get( 'ezsystems.platformui.form.type.section' ),
            $section,
            array(
                //TODO ?
                'action' => $this->get( 'router' )->generate(
                    'admin_sectionedit', array( 'sectionId' => $sectionId )
                )
            )
        );

        $form->handleRequest( $request );

        if ( $form->isValid() )
        {
            $this->sectionHelper->updateSection( $sectionToUpdate, $section );

            return $this->render(
                'eZPlatformUIBundle:Section:list.html.twig',
                array(
                    'sectionInfoList' => $this->sectionHelper->getSectionList(),
                    'canCreate' => $this->sectionHelper->canCreate(),
                )
            );
        }
        else
        {
            //TODO check if can update ?
            //TODO redirect and success message

            return $this->render(
                'eZPlatformUIBundle:Section:create.html.twig', //TODO delete edit.twig
                array(
                    'form' => $form->createView(),
                )
            );
        }
    }
}
