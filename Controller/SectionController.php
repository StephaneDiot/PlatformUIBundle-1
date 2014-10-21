<?php
/**
 * File containing the SectionController class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\PlatformUIBundle\Controller;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use EzSystems\PlatformUIBundle\Entity\Section;
use eZ\Bundle\EzPublishCoreBundle\Controller;
use EzSystems\PlatformUIBundle\Entity\SectionList;
use EzSystems\PlatformUIBundle\Form\Type\SectionListType;
use EzSystems\PlatformUIBundle\Form\Type\SectionType;
use Symfony\Component\HttpFoundation\Request;
use EzSystems\PlatformUIBundle\Helper\SectionHelperInterface;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class SectionController extends Controller
{
    /**
     * @var \EzSystems\PlatformUIBundle\Helper\SectionHelperInterface
     */
    protected $sectionHelper;

    /**
     * @var \EzSystems\PlatformUIBundle\Form\Type\SectionType
     */
    protected $sectionType;

    /**
     * @var \EzSystems\PlatformUIBundle\Form\Type\SectionListType
     */
    protected $sectionListType;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    public function __construct(
        SectionHelperInterface $sectionHelper,
        SectionType $sectionType,
        RouterInterface $router,
        TranslatorInterface $translator,
        SectionListType $sectionListType
    )
    {
        $this->sectionHelper = $sectionHelper;
        $this->sectionType = $sectionType;
        $this->router = $router;
        $this->translator = $translator;
        $this->sectionListType = $sectionListType;
    }

    /**
     * Renders the section list
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        try
        {
            return $this->render(
                'eZPlatformUIBundle:Section:list.html.twig',
                array(
                    'canCreate' => $this->sectionHelper->canCreate(),
                    'form' => $this->generateDeleteForm( new SectionList() )->createView()
                )
            );
        }
        catch ( UnauthorizedException $e )
        {
            return $this->forward( 'eZPlatformUIBundle:Pjax:accessDenied' );
        }
    }

    /**
     * Deletes sections
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction( Request $request )
    {
        $sectionListToDelete = new SectionList();
        $form = $this->generateDeleteForm( $sectionListToDelete );
        $form->handleRequest( $request );

        if ( $form->isValid() )
        {
            $this->sectionHelper->deleteSectionList( $sectionListToDelete );
        }

        return $this->redirect( $this->generateUrl( 'admin_sectionlist' ) );
    }

    /**
     * Generate the form object used to delete sections
     *
     * @param \EzSystems\PlatformUIBundle\Entity\SectionList $sectionListToDelete sections to be populated/deleted
     *
     * @return \Symfony\Component\Form\Form
     */
    private function generateDeleteForm( SectionList $sectionListToDelete )
    {
        return $this->createForm(
            $this->sectionListType,
            $sectionListToDelete,
            array(
                'action' => $this->router->generate( 'admin_sectiondelete' )
            )
        );
    }

    /**
     * Renders the view of a section
     * @param mixed $sectionId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction( $sectionId )
    {
        try
        {
            $section = $this->sectionHelper->loadSection( $sectionId );
            $contentCount = $this->sectionHelper->contentCount( $section );
            return $this->render(
                "eZPlatformUIBundle:Section:view.html.twig",
                array(
                    'section' => $section,
                    'contentCount' => $contentCount,
                )
            );
        }
        catch ( UnauthorizedException $e )
        {
            return $this->forward( 'eZPlatformUIBundle:Pjax:accessDenied' );
        }
    }

    /**
     * Displays the create form and processes it once submitted.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction( Request $request)
    {
        $section = new Section();

        $form = $this->createForm(
            $this->sectionType,
            $section,
            array(
                'action' => $this->router->generate( 'admin_sectioncreate' ),
            )
        );

        $form->handleRequest( $request );

        if ( $form->isValid() )
        {
            try
            {
                $newSection = $this->sectionHelper->createSection( $section );

                return $this->redirect(
                    $this->generateUrl(
                        'admin_sectionview',
                        array( 'sectionId' => $newSection->id )
                    )
                );
            }
            catch ( UnauthorizedException $e )
            {
                return $this->forward( 'eZPlatformUIBundle:Pjax:accessDenied' );
            }
            catch ( InvalidArgumentException $e )
            {
                $this->addAlreadyExistErrorMessage();
            }
        }

        return $this->render(
            'eZPlatformUIBundle:Section:create.html.twig',
            array( 'form' => $form->createView() )
        );
    }

    /**
     * Displays the edit form and processes it once submitted.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed $sectionId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction( Request $request, $sectionId )
    {
        try
        {
            $sectionToUpdate = $this->sectionHelper->loadSection( $sectionId );
        }
        catch ( UnauthorizedException $e )
        {
            return $this->forward( 'eZPlatformUIBundle:Pjax:accessDenied' );
        }
        catch ( NotFoundException $e )
        {
            $response = new Response();
            $response->setStatusCode( 404 );

            return $this->render(
                'eZPlatformUIBundle:Section:not_found.html.twig',
                array( 'sectionId' => $sectionId ),
                $response
            );
        }

        // Loading API data
        $section = new Section();
        $section->identifier = $sectionToUpdate->identifier;
        $section->name = $sectionToUpdate->name;

        $form = $this->createForm(
            $this->sectionType,
            $section,
            array(
                'action' => $this->router->generate(
                    'admin_sectionedit', array( 'sectionId' => $sectionId )
                )
            )
        );

        $form->handleRequest( $request );

        if ( $form->isValid() )
        {
            try
            {
                $updatedSection = $this->sectionHelper->updateSection( $sectionToUpdate, $section );

                return $this->redirect(
                    $this->generateUrl(
                        'admin_sectionview',
                        array( 'sectionId' => $updatedSection->id )
                    )
                );
            }
            catch ( UnauthorizedException $e )
            {
                return $this->forward( 'eZPlatformUIBundle:Pjax:accessDenied' );
            }
            catch ( InvalidArgumentException $e )
            {
                $this->addAlreadyExistErrorMessage();
            }
        }

        return $this->render(
            'eZPlatformUIBundle:Section:edit.html.twig',
            array( 'form' => $form->createView() )
        );
    }

    /**
     * Adds a "Section already exists" message to the flashbag.
     */
    private function addAlreadyExistErrorMessage()
    {
        $this->get( 'session' )->getFlashBag()->add(
            'error',
            $this->translator->trans(
                'section.error.id_already_exist',
                array(),
                'section'
            )
        );
    }
}
