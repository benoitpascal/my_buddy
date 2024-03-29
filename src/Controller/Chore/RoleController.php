<?php

namespace App\Controller\Chore;

use App\Entity\Chore\Permission;
use App\Entity\Chore\Status;
use App\Form\Chore\RoleType;
use App\Repository\Chore\PermissionRepository;
use App\Repository\Chore\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/dashboard/role')]
class RoleController extends RightsController
{
    private PermissionRepository $permissionRepository;
    private StatusRepository $statusRepository;

    public function __construct(
        PermissionRepository $permissionRepository,
        StatusRepository $statusRepository
    )
    {
        $this->permissionRepository = $permissionRepository;
        $this->statusRepository = $statusRepository;
    }

    #[Route('/', name: 'app_role_index', methods: ['GET'])]
    public function index(StatusRepository $statusRepository): Response
    {
        $this->checkRights(Permission::CAN_VIEW);

        return $this->render('chore/role/index.html.twig', [
            'statuses' => $statusRepository->findAllRoles(),
        ]);
    }

    #[Route('/new', name: 'app_role_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_CREATE);

        $role = (new Status())
            ->setType(Status::ROLE_TYPE);
        $entityManager->persist($role);

        foreach (Permission::CONTROLLER_LIST as $controllerName) {
            $controller = $this->statusRepository->findOneBy([
                'label' => $controllerName
            ]) ?? (new Status())
                ->setType(Status::CONTROLLER_TYPE)
                ->setLabel($controllerName);

            $entityManager->persist($controller);

            $permission = (new Permission())
                ->setController($controller)
                ->setRole($role);

            $role
                ->addPermission($permission);
        }

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($role);
            $entityManager->flush();

            return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chore/role/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_show', methods: ['GET'])]
    public function show(Status $role): Response
    {
        $this->checkRights(Permission::CAN_VIEW);

        return $this->render('chore/role/show.html.twig', [
            'role' => $role,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Status $role, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_EDIT);

        foreach (Permission::CONTROLLER_LIST as $controllerName) {
            $controller = $this->statusRepository->findOneBy([
                'label' => $controllerName
            ]) ?? (new Status())
                ->setType(Status::CONTROLLER_TYPE)
                ->setLabel($controllerName);

            $entityManager->persist($controller);

            $permission = $this->permissionRepository->findOneBy(['controller' => $controller, 'role' => $role]) ?? (new Permission())
                ->setController($controller);

            if($role->getLabel() == 'admin') {
                $permission->setAccess(Permission::CAN_ALL);
            }

            $role
                ->addPermission($permission);
        }

        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('chore/role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_role_delete', methods: ['POST'])]
    public function delete(Request $request, Status $role, EntityManagerInterface $entityManager): Response
    {
        $this->checkRights(Permission::CAN_DELETE);

        if ($this->isCsrfTokenValid('delete'.$role->getId(), $request->request->get('_token'))) {
            $entityManager->remove($role);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }
}
