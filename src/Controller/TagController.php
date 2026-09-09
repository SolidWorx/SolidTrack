<?php

declare(strict_types=1);

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use App\Repository\TagRepository;
use SolidWorx\Platform\PlatformBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/tags')]
final class TagController extends BaseController
{
    #[Route(path: '/', name: 'app_tag_index', methods: ['GET'])]
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $tagRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route(path: '/new', name: 'app_tag_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TagRepository $tagRepository): Response
    {
        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tagRepository->save($tag);
            $this->addFlash('success', 'Tag added successfully');

            return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tag/new.html.twig', [
            'tag' => $tag,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'app_tag_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tag $tag, TagRepository $tagRepository): Response
    {
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tagRepository->save($tag);
            $this->addFlash('success', 'Tag updated successfully');

            return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tag/edit.html.twig', [
            'tag' => $tag,
            'form' => $form,
        ]);
    }

    #[Route(path: '/{id}/delete', name: 'app_tag_delete', methods: ['POST'])]
    public function delete(Request $request, Tag $tag, TagRepository $tagRepository): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete' . $tag->getId(), $request->getPayload()->getString('_token'))) {
            $tagRepository->remove($tag);
        }

        return $this->redirectToRoute('app_tag_index', [], Response::HTTP_SEE_OTHER);
    }
}
