<?php

/*
 * This file is part of Phraseanet SDK.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhraseanetSDK\Repository;

use PhraseanetSDK\Exception\RuntimeException;
use PhraseanetSDK\EntityHydrator;

class User extends AbstractRepository
{
    /**
     * @return \PhraseanetSDK\Entity\User
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     * @deprecated Use User::me() instead
     */
    public function findMe()
    {
        return $this->me();
    }

    /**
     * @return \PhraseanetSDK\Entity\User
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function me()
    {
        $response = $this->query('GET', 'me/');

        if (! $response->hasProperty('user')) {
            throw new RuntimeException('Missing "user" property in response content');
        }

        /** @var \PhraseanetSDK\Entity\User $user */
        $user = EntityHydrator::hydrate('user', $response->getProperty('user'), $this->em);

        if ($response->hasProperty('collections')) {
            $user->setCollectionRights($response->getProperty('collections'));
        }

        return $user;
    }

    /**
     * @param $emailAddress
     * @return string
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function requestPasswordReset($emailAddress)
    {
        $response = $this->query('POST', 'accounts/reset-password/' . $emailAddress . '/');

        if (! $response->hasProperty('reset_token')) {
            throw new RuntimeException('Missing "token" property in response content');
        }

        return (string) $response->getProperty('reset_token');
    }

    /**
     * @param $token
     * @param $password
     * @return bool
     * @throws \PhraseanetSDK\Exception\NotFoundException
     * @throws \PhraseanetSDK\Exception\UnauthorizedException
     */
    public function resetPassword($token, $password)
    {
        $response = $this->query('POST', 'accounts/update-password/' . $token . '/', array(), array(
           'password' => $password
        ));

        if (! $response->hasProperty('success')) {
            throw new RuntimeException('Missing "success" property in response content');
        }

        return (bool) $response->getProperty('success');
    }
}
