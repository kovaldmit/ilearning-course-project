<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private FactoryInterface $factory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private AuthorizationCheckerInterface $authorizationChecker;

    private Security $security;

    /**
     * @param FactoryInterface $factory
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param Security $security
     */
    public function __construct(
        FactoryInterface $factory,
        AuthorizationCheckerInterface $authorizationChecker,
        Security $security
    ) {
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
        $this->security = $security;
    }

    /**
     * @param array $options
     * @return ItemInterface
     */
    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Collections', ['route' => 'collection_container_index']);
        $menu->addChild('Tags', ['route' => 'tag_index']);
        $menu->addChild('Search', ['route' => 'search']);

        return $menu;
    }

    /**
     * @param array $options
     * @return ItemInterface
     */
    public function createAccountMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('account');

        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->security->getUser();

            $dropdown = $menu->addChild('Dropdown')
                ->setLabel($user->getEmail())
                ->setAttributes(['class' => 'dropdown'])
                ->setLinkAttributes([
                    'class' => 'nav-link dropdown-toggle',
                    'data-bs-toggle' => 'dropdown',
                    'aria-expanded' => 'false',
                ]);

            $dropdown->addChild('Profile', [
                'route' => 'user_show',
                'routeParameters' => ['id' => $user->getId()]
            ]);

            $dropdown->addChild('Logout', ['route' => 'app_logout']);
        } else {
            $menu->addChild('Log in', ['route' => 'app_login']);
            $menu->addChild('Register', ['route' => 'app_register']);
        }

        return $menu;
    }

    /**
     * @param array $options
     * @return ItemInterface
     */
    public function createAdminMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('admin');

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $dropdown = $menu->addChild('Dropdown')
                ->setLabel('Admin Menu')
                ->setAttributes(['class' => 'dropdown'])
                ->setLinkAttributes([
                    'class' => 'nav-link dropdown-toggle',
                    'data-bs-toggle' => 'dropdown',
                    'aria-expanded' => 'false',
                ]);

            $dropdown->addChild('Users', ['route' => 'user_index']);
            $dropdown->addChild('Categories', ['route' => 'collection_category_index']);
            $dropdown->addChild('Collections', ['route' => 'collection_container_index']);
            $dropdown->addChild('Items', ['route' => 'collection_item_index']);
        }

        return $menu;
    }
}
